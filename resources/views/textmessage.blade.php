    @extends('base')

    @section('content')



        <div class="row">
            <div class="col-sm-8">
                <h4>OutBox</h4>

            </div>
        </div>

<div class="table-responsive">


<table id="example" class="display" style="width:100%">
    <thead>
        <tr>
            <th>#</th>
            <th>Phone Number</th>
            <th>Message</th>

            <th>Message Id</th>
            <th>Status</th>
            <th>TAT</th>
            <th>Delivery date</th>
            <th>Date</th>

        </tr>
    </thead>
    <tbody>
        @if(!empty($messages) && $messages->count())
        @php $count=0 @endphp
            @foreach($messages as $key => $value)
            @php $count++ @endphp
                <tr>
                    <td>{{ $count }}</td>
                    <td>{{ $value->phone_number }}</td>
                    <td>{{ $value->text_message }}</td>

                    <td>{{ $value->message_id }}</td>

                    <td>{{ $value->status }}</td>
                    <td>{{ $value->delivery_tat }}</td>
                    <td>{{ $value->delivery_time }}</td>
                    <td>{{ $value->created_at }}</td>

                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="10">There are no data.</td>
            </tr>
        @endif
    </tbody>
</table>
</div>


{{$messages->links("pagination::bootstrap-4")}}
{{-- <script>
    $(document).ready(function() {
    $('#example').DataTable(
        {
  "pageLength": 100
}
    );
} );
</script> --}}

 @endsection
{{-- </body>
</html> --}}
