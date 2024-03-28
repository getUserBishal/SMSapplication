<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\SentTextMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RoyceLtd\LaravelBulkSMS\Facades\RoyceBulkSMS;
use Ixudra\Curl\Facades\Curl;
use PHPUnit\Framework\Attributes\Group;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function message_dashboard()
    {
        $contacts = Contact::all();
        $groups = ContactGroup::all();
        return view('message_dashboard', compact('contacts'), compact('groups'));
    }

    public function messages()
    {
        $messages = SentTextMessage::orderBy('id', 'desc')->paginate(50);
        //dd($messages);
        return view('textmessage', ['messages' => $messages]);
    }

    public function deliveryReport(Request $request){
        // Log::info($request);
        $txt = SentTextMessage::where('message_id', '=', $request->unique_id)->first();
        $txt->delivery_status = $request->delivery_status;
        $txt->delivery_code = $request->delivery_status;
        $txt->delivery_description = $request->delivery_description;
        $txt->delivery_response_description = $request->delivery_response_description;
        $txt->delivery_network_id = $request->delivery_network_id;
        $txt->delivery_tat = $request->delivery_tat;
        $txt->delivery_time = $request->delivery_time;
        $txt->save();
    }

    public function base(){
        //dd('');
        return view('base');

    }

    public function contacts(){
        $contacts= Contact::join('contact_groups','contact_groups.id','=','contacts.group_id')
        ->select('contacts.*','contact_groups.name as group')
        ->get();
        // dd($apikey);
        $groups=ContactGroup::all();
        return view('contacts',['contacts'=>$contacts,'status'=>'My Contact','groups'=>$groups]);

    }

    public function deleteContact($id){

        $contact=Contact::find($id)->delete();
        return back()->with('status','Contact deleted succesfully');
        // return view('contacts',['status'=>'Contact deleted succesfully']);

    }

    public function contactsGroup(){
        $groups= ContactGroup::withCount('contacts')->get();
        // dd($groups);
        // dd($apikey);
        return view('contactgroups',['groups'=>$groups]);

    }

    public function editGroup($id){
        $group= ContactGroup::find($id);

        // $groups= ContactGroup::withCount('contacts')->get();
        return view('editgroup',['group'=>$group]);
    }

    public function editContactGroup(Request $request){
        $group= ContactGroup::find($request->id);
        $group->name=$request->group;
        $group->save();

        return redirect('contacts-group');

    }

    public function saveContacts(Request $request){
        // dd($request->all());
        // $this->validate($request,[
        //     'first_namesf'=>'required'

        // ]);
        $contact= new Contact();
        $contact->first_name=$request->first_name;
        $contact->other_names=$request->other_names;
        $contact->phone_number=$request->phone_number;
        $contact->alt_phone_number=$request->alt_phone_number;
        $contact->group_id=$request->group;
        $contact->email=$request->email;
        $contact->save();

        return back()->with('status','Contact added successfully');

    }

    public function saveContactsGroup(Request $request){
        $this->validate($request,[
            'group'=>'required|unique:contact_groups,name'

        ]);
        $group=new ContactGroup();
        $group->name=$request->group;
        $group->description=$request->description;
        $group->save();

        return back()->with('status','Group saved successfully');

    }

    public function singleText(){

        return view('singletext');
    }

    public function sendSingleText(Request $request){
        // $phone = "9843820568";
        // $message= $request->message;

        $phone_number = explode("\n", $request->phone_numbers);

        // dd($phone_number);

        foreach($phone_number as $phone){
             RoyceBulkSMS::sendSMS($phone, $request->message);

        }
        return redirect('dashboard')->with('status','SMS sent successfully');

    }

    public function contactsText(){
        $contacts= Contact::all();

        return view('contacttext',['contacts'=>$contacts]);

    }


    public function sendContactsText(Request $request)
    {
        $token = '<token-provided>'; // Replace with your Sparrow SMS API token
        $identity = '<Identity>'; // Replace with your sender identity
        $recipientNumbers = implode(',', $request->phone_numbers);

        $args = http_build_query(array(
            'token' => $token,
            'from'  => $identity,
            'to'    => $recipientNumbers,
            'text'  => $request->message
        ));

        $url = "http://api.sparrowsms.com/v2/sms/";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Execute cURL request
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status_code == 200) {
            // SMS sent successfully
            return redirect('dashboard')->with('status', 'SMS sent successfully');
        } else {
            // Failed to send SMS
            return redirect('dashboard')->with('error', 'Failed to send SMS');
        }
    }

    public function groupText(){

        $groups= ContactGroup::all();

        return view('grouptext',['groups'=>$groups]);

    }

    public function sendGroupText(Request $request){

        // dd($request->all());

        foreach($request->groups as $group){
            // $separate= explode('}',$phone);

            $contacts= Contact::where('group_id',$group)->get();

            foreach($contacts as $contact){
                if($request->salutation=='Yes'){
                $message="Hello $contact->first_name, $request->message";

            }else{
                $message=$request->message;

            }


            RoyceBulkSMS::sendSMS($contact->phone_number,$message );

            }

            // dd($message);
        }

        return redirect('dashboard')->with('status','SMS sent successfully');

    }

    public function getDeliveryReport(){

        return view('deliveryreport',['status'=>'Enter message ID from outbox']);

    }

    public function pDeliveryReport(Request $request){

        $url = 'https://roycebulksms.com/api/delivery-report';
        $apikey = env('API_KEY');
        $response = Curl::to($url)
            ->withData(array(
                'message_id' => $request->message_id,
                'sender_id' => env('SENDER_ID')
                // 'text_message' => $message
            ))
            ->withBearer($apikey)
            ->post();
                Log::info($response);


            if(!$response){

                return view('deliveryreport',['status'=>'Check the message id and try again']);


            }
            $res = json_decode($response);


            $text= SentTextMessage::where('message_id',$request->message_id)->first();
            $text->delivery_status=$res->delivery_status;
            $text->status=$res->delivery_status;
            $text->delivery_tat=$res->delivery_tat;
            $text->delivery_time=$res->delivery_time;

            $text->save();

            return view('deliveryreport',['status'=>'Delivery Report','report'=>$res]);


    }

    public function setWebhook(){
        return view('webhook',['status'=>'Set Web hook URL']);

    }

    public function receiveDeliveryReport(Request $request)
    {
        //log::info($request->all());

        $res = sentTextMessage::where('message_id',$request->message_id)->first();
        $res->delivery_time=$request->delivery_time;
        $res->delivery_tat=$request->delivery_tat;
        $res->delivery_description=$request->delivery_description;
        $res->save();
    }
}
