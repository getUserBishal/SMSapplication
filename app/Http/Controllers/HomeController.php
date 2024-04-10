<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\SentTextMessage;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel;

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
        return view('textmessage', ['messages' => $messages]);
    }

    public function deliveryReport(Request $request){
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
        return view('base');

    }

    public function contacts(){
        $contacts= Contact::join('contact_groups','contact_groups.id','=','contacts.group_id')
        ->select('contacts.*','contact_groups.name as group')
        ->get();
        $groups=ContactGroup::all();
        return view('contacts',['contacts'=>$contacts,'status'=>'My Contact','groups'=>$groups]);

    }

    public function deleteContact($id){

        $contact=Contact::find($id)->delete();
        return back()->with('status','Contact deleted succesfully');

    }

    public function contactsGroup(){
        $groups= ContactGroup::withCount('contacts')->get();
        return view('contactgroups',['groups'=>$groups]);

    }

    public function editGroup($id){
        $group= ContactGroup::find($id);
        return view('editgroup',['group'=>$group]);
    }

    public function editContactGroup(Request $request){
        $group= ContactGroup::find($request->id);
        $group->name=$request->group;
        $group->save();

        return redirect('contacts-group');

    }

    public function saveContacts(Request $request){
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

            $selectedPhoneNumbers = $request->selected_phone_number;
            $message = $request->single_message;

            foreach ($selectedPhoneNumbers as $phoneNumber) {
                $response = $client->post('https://sms.sociair.com/api/sms', [
                    'headers' => [
                        'Authorization' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyIiwianRpIjoiZDJhYWY4MzU3MDQ2ODhhY2JlNjRhZmM0YmQzYmExODdjZjhiMGJlYTcxZTZiYzRmZTE3YmQ2ZTU2MzU0NDYyNDFmYWRkODZkMDhhODY3OWIiLCJpYXQiOjE3MTIwMzUzODcuODA3OTgsIm5iZiI6MTcxMjAzNTM4Ny44MDc5ODYsImV4cCI6MTc0MzU3MTM4Ny43ODA5OTcsInN1YiI6IjEyNTciLCJzY29wZXMiOltdfQ.eJS_NUDVvuTrheHlcd8t8Sronp6DMTd2FC5KAWZBOwzCLMAbxQdwlYNFgRshsea9CB-bC3O1ORIJ0_SdPc3n7LtyiNb1chqGBRqJ018HUxU2ljl8GbKKzGo_zNsr9UuRKp4oEw5t40dPXCgmpKwaxooHfwx75p9YjOU072wO6KhAYl-I0sl5WIIcyOJuqxZiBqT3nnTYaFzitpKU3sAX0NEXT4L5wbrZt-mDbyUatifWVBS3VpjdBfTDPz4yH6y_2NoiNwePVhnqIUba0YykPAbALQdvP5bfPkAi3GoxoTCsagUR-Dcvk40WNd1I_vRO2YAdzwr9-9Cl-UFzo8E9Y1EnxWIUeR5mXb5l6iGVQ5bHxqtpQsTU-9WvN-1w1dzebZAAqJ6QD0DR2tPCZ4ZEDnXZK6KDPV4gWsscaieR3hMiJ84ct0VfuUnp18yC1VmVTd9_1F-YOpCEdBtGCo7TSK1kxGkNQwq3FCIAEeatx1lsbP-e9nWrEEP3jZklgEohF_W8wvyY5hEzXVQY1qwh7Z47XVxtwE6eFG3QTdo4BRtp8ccMFqY9l5JZQXdxFOANsngqwcDFmt-DDzCwev-EcXtCSBscOOstjh7lk6IWCdWP5qqelHV7RR9QsgFwUazEZoKW33yjLNjCbQ0QN2jJEEHCAfRXjzr4gGbfmVN0V6M',
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'message' => $message,
                        'mobile' => $phoneNumber,
                    ],
                ]);

                if ($response->getStatusCode() == 200) {
                    $responseData = json_decode($response->getBody(), true);
                    Log::info('SMS sent successfully to ' . $phoneNumber . ': ' . $responseData['message']);
                } else {
                    Log::error('Failed to send SMS to ' . $phoneNumber . ': ' . $response->getBody());
                }
            }

            return redirect('landing')->with('status', 'SMS sent successfully');
        } catch (\Exception $e) {
            Log::error('Exception while sending SMS: ' . $e->getMessage());
            return redirect('landing')->with('status', 'Failed to send SMS');
        }
    }

    public function fetchGroupNumbers(Request $request)
{
    $groupId = $request->query('group');

    $groupNumbers = Contact::where('group_id', $groupId)->pluck('phone_number')->toArray();

    return response()->json($groupNumbers);
}


    public function groupText(){

        $groups= ContactGroup::all();

        return view('grouptext',['groups'=>$groups]);

    }

public function sendGroupText(Request $request) {
    $message = $request->message;
    $mobileNumbers = $request->group_selectedNumbers;

    if ($message && $mobileNumbers) {
        $client = new Client();

        try {
            $requestData = [
                'message' => $message,
                'mobile' => implode(',', $mobileNumbers)
            ];

            $response = $client->post('https://sms.sociair.com/api/sms', [
                'headers' => [
                    'Authorization' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyIiwianRpIjoiZDJhYWY4MzU3MDQ2ODhhY2JlNjRhZmM0YmQzYmExODdjZjhiMGJlYTcxZTZiYzRmZTE3YmQ2ZTU2MzU0NDYyNDFmYWRkODZkMDhhODY3OWIiLCJpYXQiOjE3MTIwMzUzODcuODA3OTgsIm5iZiI6MTcxMjAzNTM4Ny44MDc5ODYsImV4cCI6MTc0MzU3MTM4Ny43ODA5OTcsInN1YiI6IjEyNTciLCJzY29wZXMiOltdfQ.eJS_NUDVvuTrheHlcd8t8Sronp6DMTd2FC5KAWZBOwzCLMAbxQdwlYNFgRshsea9CB-bC3O1ORIJ0_SdPc3n7LtyiNb1chqGBRqJ018HUxU2ljl8GbKKzGo_zNsr9UuRKp4oEw5t40dPXCgmpKwaxooHfwx75p9YjOU072wO6KhAYl-I0sl5WIIcyOJuqxZiBqT3nnTYaFzitpKU3sAX0NEXT4L5wbrZt-mDbyUatifWVBS3VpjdBfTDPz4yH6y_2NoiNwePVhnqIUba0YykPAbALQdvP5bfPkAi3GoxoTCsagUR-Dcvk40WNd1I_vRO2YAdzwr9-9Cl-UFzo8E9Y1EnxWIUeR5mXb5l6iGVQ5bHxqtpQsTU-9WvN-1w1dzebZAAqJ6QD0DR2tPCZ4ZEDnXZK6KDPV4gWsscaieR3hMiJ84ct0VfuUnp18yC1VmVTd9_1F-YOpCEdBtGCo7TSK1kxGkNQwq3FCIAEeatx1lsbP-e9nWrEEP3jZklgEohF_W8wvyY5hEzXVQY1qwh7Z47XVxtwE6eFG3QTdo4BRtp8ccMFqY9l5JZQXdxFOANsngqwcDFmt-DDzCwev-EcXtCSBscOOstjh7lk6IWCdWP5qqelHV7RR9QsgFwUazEZoKW33yjLNjCbQ0QN2jJEEHCAfRXjzr4gGbfmVN0V6M',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $requestData,
            ]);

            if ($response->getStatusCode() === 200) {
                $responseData = json_decode($response->getBody(), true);
                Log::info('SMS sent successfully:', $responseData);
                return redirect('landing')->with('status', 'SMS sent successfully');
            } else {
                Log::error('SMS sending failed:', $response->getBody());
                return redirect('landing')->with('status', 'Failed to send SMS');
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending SMS:', $e->getMessage());
            return redirect('landing')->with('status', 'Failed to send SMS');
        }
    } else {
        Log::error('Message or mobile numbers are missing.');
        return redirect('landing')->with('status', 'Message or mobile numbers are missing.');
    }
}

public function sendBulkSMS(Request $request)
{
    $validatedData = $request->validate([
        'phone_numbers_file' => 'required|file|mimes:txt,csv,xlsx,xls',
        'bulk_message' => 'required|string',
    ]);

    // Get the phone numbers from the uploaded file
    $phoneNumbers = $this->getPhoneNumbersFromFile($validatedData['phone_numbers_file']);

    // Send the message to each phone number
    $client = new Client();
    $message = $validatedData['bulk_message'];

    try {
        foreach ($phoneNumbers as $mobileNumber) {
            $requestData = [
                'message' => $message,
                'mobile' => $mobileNumber,
            ];

            $response = $client->post('https://sms.sociair.com/api/sms', [
                'headers' => [
                    'Authorization' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyIiwianRpIjoiZDJhYWY4MzU3MDQ2ODhhY2JlNjRhZmM0YmQzYmExODdjZjhiMGJlYTcxZTZiYzRmZTE3YmQ2ZTU2MzU0NDYyNDFmYWRkODZkMDhhODY3OWIiLCJpYXQiOjE3MTIwMzUzODcuODA3OTgsIm5iZiI6MTcxMjAzNTM4Ny44MDc5ODYsImV4cCI6MTc0MzU3MTM4Ny43ODA5OTcsInN1YiI6IjEyNTciLCJzY29wZXMiOltdfQ.eJS_NUDVvuTrheHlcd8t8Sronp6DMTd2FC5KAWZBOwzCLMAbxQdwlYNFgRshsea9CB-bC3O1ORIJ0_SdPc3n7LtyiNb1chqGBRqJ018HUxU2ljl8GbKKzGo_zNsr9UuRKp4oEw5t40dPXCgmpKwaxooHfwx75p9YjOU072wO6KhAYl-I0sl5WIIcyOJuqxZiBqT3nnTYaFzitpKU3sAX0NEXT4L5wbrZt-mDbyUatifWVBS3VpjdBfTDPz4yH6y_2NoiNwePVhnqIUba0YykPAbALQdvP5bfPkAi3GoxoTCsagUR-Dcvk40WNd1I_vRO2YAdzwr9-9Cl-UFzo8E9Y1EnxWIUeR5mXb5l6iGVQ5bHxqtpQsTU-9WvN-1w1dzebZAAqJ6QD0DR2tPCZ4ZEDnXZK6KDPV4gWsscaieR3hMiJ84ct0VfuUnp18yC1VmVTd9_1F-YOpCEdBtGCo7TSK1kxGkNQwq3FCIAEeatx1lsbP-e9nWrEEP3jZklgEohF_W8wvyY5hEzXVQY1qwh7Z47XVxtwE6eFG3QTdo4BRtp8ccMFqY9l5JZQXdxFOANsngqwcDFmt-DDzCwev-EcXtCSBscOOstjh7lk6IWCdWP5qqelHV7RR9QsgFwUazEZoKW33yjLNjCbQ0QN2jJEEHCAfRXjzr4gGbfmVN0V6M',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $requestData,
            ]);

            if ($response->getStatusCode() !== 200) {
                Log::error('SMS sending failed:', $response->getBody());
                // Handle failure case
            }
        }

        Log::info('SMS sent successfully');
        return redirect('landing')->with('status', 'SMS sent successfully');
    } catch (\Exception $e) {
        Log::error('Exception while sending SMS:', $e->getMessage());
        return redirect('landing')->with('status', 'Failed to send SMS');
    }
}

private function getPhoneNumbersFromFile($file)
{
    $extension = $file->getClientOriginalExtension();

    switch ($extension) {
        case 'txt':
            $numbers = preg_split('/\r\n|\r|\n/', file_get_contents($file));
            break;
        case 'csv':
        case 'xlsx':
        case 'xls':
            $numbers = Excel::toArray(null, $file)[0];
            break;
        default:
            return [];
    }

    // Remove any non-numeric values
    return array_filter($numbers, function ($value) {
        return is_numeric($value);
    });
}


 }
