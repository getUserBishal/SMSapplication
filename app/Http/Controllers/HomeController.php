<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\SentTextMessage;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Log;

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
        return view('landing');
    }

    public function message_dashboard()
    {
        $contacts = Contact::all();
        $groups = ContactGroup::all();
        return view('message_dashboard', compact('contacts'), compact('groups'));
    }

    public function landing()
    {
        $contacts = Contact::all();
        $groups = ContactGroup::all();
        return view('landing', compact('contacts'), compact('groups'));
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

    public function contactsText(){
        $contacts= Contact::all();

        return view('contacttext',['contacts'=>$contacts]);

    }

    public function sendContactsText(Request $request) {
        $client = new Client();

    try {

        if ($request->salutation == 'No') {
            $message = "Hello $request->first_name, $request->message";
        } else {
            $message = $request->message;
        }

        $response = $client->post('https://sms.sociair.com/api/sms', [
            'headers' => [
                'Authorization' => 'Bearer your_bearer_token',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'message' => $message,
                'mobile' => $request->phone_number,
            ],
        ]);

        if ($response->getStatusCode() == 200) {

            $responseData = json_decode($response->getBody(), true);

            Log::info('SMS sent successfully: ' . $responseData['message']);
        } else {
            Log::error('Failed to send SMS: ' . $response->getBody());
        }
    } catch (\Exception $e) {
        Log::error('Exception while sending SMS: ' . $e->getMessage());
    }

    return redirect('landing')->with('status', 'SMS sent successfully');
    }


    public function groupText(){

        $groups= ContactGroup::all();

        return view('grouptext',['groups'=>$groups]);

    }
 }
