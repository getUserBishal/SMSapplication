{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Royce Bulk SMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css"/>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js" > </script>
</head>
<body> --}}
    @extends('base')

    @section('content')



        <div class="row">
            <div class="col-sm-12 text-center">
                <h4>Group SMS</h4>

            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-secondary alert-dismissible fade show" role="alert">
   If you would like to customize text for example Hello John ..... allow to use of salutation
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
            </div>
        </div>

        <div style="background-color: rgb(228, 228, 228); padding: 20px; max-width: 600px; margin: 0 auto;">
            <form method="POST" action="{{ url('group-text') }}">
                {{ csrf_field() }}

                <!-- Removed row class -->
                <div>
                    <div>
                        <div class="form-group">
                            <label>Message</label>
                            <textarea class="form-control" name="message" rows="5" required oninput="updateInfo(this.value)"></textarea>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <div class="form-group">
                                <label>Include Salutation?</label>
                                <div>
                                    <div>
                                        <input class="form-check-input" type="radio" name="salutation" id="inlineRadio1" value="Yes" checked>
                                        <label class="form-check-label" for="inlineRadio1">Yes</label>
                                    </div>
                                    <div>
                                        <input class="form-check-input" type="radio" name="salutation" id="inlineRadio2" value="No">
                                        <label class="form-check-label" for="inlineRadio2">No</label>
                                    </div>
                                </div>
                            </div>

                    </div>
                    <div>
                        <div class="form-group">
                            <label for="groupSelect">Select Group:</label>
                            <select class="form-control" id="groupSelect" name="selected_group">
                                <option value="">Select a group</option>
                                @foreach ($groups as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <input type="submit" class="btn btn-primary" value="Send SMS">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        </div>

        <div id="infoContainer" style="background-color: rgb(228, 228, 228); padding: 20px; max-width: 600px; margin: 20px auto;">
            <p id="charCount">Characters: 0 / 160</p>
            <p id="smsCount">SMS Count: 0</p>
            <p id="numInfo">Numbers: 0 (NT 0 NC 0)</p>
            <p id="rateInfo">Rate: NT 0.2 NC 0.2</p>
            <p id="totalCost">Total Cost (0.00 + 0.00 +): 0.00</p>
        </div>

        <script>
            function updateInfo(message) {
                var charCount = message.length;
                var smsCount = Math.ceil(charCount / 160);
                document.getElementById('charCount').innerText = "Characters: " + charCount + " / 160";
                document.getElementById('smsCount').innerText = "SMS Count: " + smsCount;
                var totalCost = (smsCount * 0.2).toFixed(2);
                document.getElementById('totalCost').innerText = "Total Cost (" + smsCount + " * 0.20 +): " + totalCost;
            }

            function updateNumbers() {
                var checkboxes = document.getElementsByName('groups[]');
                var numCount = 0;
                var ntcCount = 0;
                var ncellCount = 0;

                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].checked) {
                        numCount++;
                        var phoneNumber = checkboxes[i].value;
                        // Check if the phone number is NTC or Ncell
                        if (phoneNumber.startsWith("98")) { // Assuming NTC numbers start with 98
                            ntcCount++;
                        } else if (phoneNumber.startsWith("98")) { // Assuming Ncell numbers start with 98
                            ncellCount++;
                        }
                    }
                }
                document.getElementById('numInfo').innerText = "Numbers: " + numCount + " (NT " + ntcCount + " NC " + ncellCount + ")";
            }
        </script>




{{-- {{$messages->links("pagination::bootstrap-4")}} --}}
<script>
    $(document).ready(function() {
    $('#example').DataTable();
} );
</script>

 @endsection
{{-- </body>
</html> --}}
