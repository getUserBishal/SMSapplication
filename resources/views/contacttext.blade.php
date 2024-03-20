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
            <h4>Single SMS</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                Check Salutation for customization in text.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
    <div style="background-color: rgb(228, 228, 228); padding: 20px; max-width: 600px; margin: 0 auto;">
        <form method="POST" action="{{ url('contacts-text') }}">
            {{ csrf_field() }}

            <div>
                <label>Message</label>
                <textarea class="form-control" name="message" rows="3" required oninput="updateInfo(this.value)"></textarea>
            </div>

            <div>
                <label>Include Salutation?</label>
                <div>
                    <input type="radio" name="salutation" value="Yes" checked> Yes
                    <input type="radio" name="salutation" value="No"> No
                </div>
            </div>

            <div>
                <input type="checkbox" id="checkAll"> Select all ({{ count($contacts) }})
            </div>

            <div>
                <label>Phone Numbers</label>
                @foreach ($contacts as $item)
                    <div>
                        <input type="checkbox" value="{{ $item->phone_number }}" name="selected_phone_number" onclick="updateNumbers()">
                        {{ $item->phone_number }} {{ $item->first_name }}
                    </div>
                @endforeach
            </div>

            <div>
                <input type="submit" class="btn btn-primary" value="Send SMS">
            </div>
        </form>
    </div>

    <div id="infoContainer" style="background-color: rgb(228, 228, 228); padding: 20px; max-width: 600px; margin: 20px auto;">
        <p id="charCount">Characters: 0 / 160</p>
        <p id="smsCount">SMS Count: 0</p>
        <p id="numInfo">Numbers: 0 (NT 0 NC 0)</p>
        <p id="rateInfo">Rate: NT 0.2 NC 0.2</p>
        <p id="totalCost">Total Cost (0.00 + 0.00 +): 0.00</p>
    </div>

    <script>

        let rate = 0.2;
        let totalRate = 0;
        let smsCount = 0;
        function updateNumbers() {
            var selectedNumbers = document.querySelectorAll('input[name=selected_phone_number]:checked');
            var ntCount = Array.from(selectedNumbers).filter(num => getOperatorType(num.value) === 'NTC').length;
            var ncCount = Array.from(selectedNumbers).filter(num => getOperatorType(num.value) === 'NCELL').length;

            document.getElementById('numInfo').innerText = `Numbers: ${selectedNumbers.length} (NT ${ntCount} NC ${ncCount})`;
        }

        function getOperatorType(mobil) {
            let mobile = mobil.trim();
            let ntc_regex = /9[78][456][0-9]{7}/;
            let ncel_regex = /98[012][0-9]{7}/;

            if (mobile.match(ntc_regex)) {
                return 'NTC';
            } else if (mobile.match(ncel_regex)) {
                return 'NCELL';
            } else {
                return '';
            }
        }



        function updateInfo(text) {
            var charCount = text.length;
            var smsCount = 0;
            var remainingChars = charCount;

            if (charCount <= 160) {
                smsCount = 1;
                remainingChars = 160 - charCount;
            } else if (charCount <= 306) {
                smsCount = 2;
                remainingChars = 306 - charCount;
            } else if (charCount <= 459) {
                smsCount = 3;
                remainingChars = 459 - charCount;
            } else {
                smsCount = 3;
                remainingChars = 152;
                console.log(smsCount,remainingChars);

                while (charCount > (459 + (smsCount - 3) * 152)) {
                    smsCount++;
                    remainingChars = Math.abs(charCount - (459 + (smsCount - 3) * 152));
                }
        }

        document.getElementById('charCount').innerText = `Characters: ${charCount} / ${remainingChars}`;
        document.getElementById('smsCount').innerText = 'SMS Count: ' + smsCount;
        document.getElementById('rateInfo').innerText = `Rate: NT ${rate} NC ${rate}`;

        var totalRate = smsCount * rate;
        document.getElementById('totalCost').innerText = `Total Cost (${rate.toFixed(2)} + ${rate.toFixed(2)}): ${(totalRate).toFixed(2)}`;
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
