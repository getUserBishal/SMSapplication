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
            <div class="col-sm-12">
                <h4>Bulk SMS</h4>


            </div>
        </div>
        <div class="alert alert-secondary">
            While copy and pasting number from files there should be one number in each line.
        </div>
        <div style="background-color: rgb(228, 228, 228); padding: 20px; max-width: 600px; margin: 0 auto;">

            <form action="{{ url('single-text') }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}

                <!-- Removed row class -->
                <div>
                    <div>
                        <div class="form-group">
                            <label>Show in Nepali</label>
                            <input type="checkbox" id="showNepali" onchange="toggleNepali()">
                        </div>

                        <div class="form-group">
                            <label>Message</label>
                            <textarea class="form-control" name="message" id="message" rows="5" required oninput="updateInfo(this.value); updateNumbers()"></textarea>
                        </div>

                        <div id="nepaliText" style="display: none;">
                            <!-- Display translated Nepali text here -->
                        </div>

                        <script>
                            // Function to update Nepali translation
                            function updateNepali() {
                                // Get the text from the textarea
                                var englishText = document.getElementById('message').value;

                                // Perform translation to Nepali
                                var nepaliText = translateToNepali(englishText); // You need to define this function

                                // Update the display with Nepali text
                                document.getElementById('nepaliText').textContent = nepaliText;
                            }

                            // Function to toggle the visibility of Nepali text
                            function toggleNepali() {
                                var checkbox = document.getElementById('showNepali');
                                var nepaliDiv = document.getElementById('nepaliText');

                                if (checkbox.checked) {
                                    // Show Nepali text
                                    nepaliDiv.style.display = 'block';
                                    updateNepali();
                                } else {
                                    // Hide Nepali text
                                    nepaliDiv.style.display = 'none';
                                }
                            }
                        </script>

                    </div>
                    <div>
                        <div class="form-group">
                            <label>Phone Numbers</label>
                            <input type="file" class="form-control-file" name="phone_numbers_file" id="phoneNumbersFile" accept=".txt,.csv,.xlsx,.xls" onchange="showSelectedNumbers(); updateNumbers()">
                            <small class="form-text text-muted">Accepted formats: .txt, .csv, .xlsx, .xls</small>
                            <!-- Input field to display and enter numbers -->
                            <textarea class="form-control" id="phoneNumbersInput" name="phone_numbers" rows="5" placeholder="Enter or upload phone numbers" required oninput="updateNumbers()"></textarea>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <input type="submit" class="btn btn-primary" value="Send SMS">
                        </div>
                    </div>
                </div>
            </form>

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
                let ntCount = 0; // Declare ntCount as a global variable
                let ncCount = 0; // Declare ncCount as a global variable

                function showSelectedNumbers() {
                    var fileInput = document.getElementById('phoneNumbersFile');
                    var selectedFile = fileInput.files[0];
                    var phoneNumbersInput = document.getElementById('phoneNumbersInput');

                    if (selectedFile) {
                        var reader = new FileReader();
                        reader.onload = function(event) {
                            var fileContent = event.target.result;
                            var numbers = fileContent.split('\n').map(function(number) {
                                return number.trim();
                            });

                            // Save the uploaded numbers to the input field
                            var existingNumbers = phoneNumbersInput.value.trim();
                            if (existingNumbers !== '') {
                                existingNumbers += '\n'; // Add a newline separator if there are existing numbers
                            }
                            phoneNumbersInput.value = existingNumbers + numbers.join('\n');

                            // Update the numbers count and operator type
                            updateNumbers();
                        };
                        reader.readAsText(selectedFile);
                    }
                }

                function updateNumbers() {
                    var phoneNumbersInput = document.getElementById('phoneNumbersInput');
                    var allNumbers = phoneNumbersInput.value.split('\n');

                    ntCount = 0; // Reset ntCount
                    ncCount = 0; // Reset ncCount

                    allNumbers.forEach(function(number) {
                        var operatorType = getOperatorType(number);
                        if (operatorType === 'NTC') {
                            ntCount++;
                        } else if (operatorType === 'NCELL') {
                            ncCount++;
                        }
                    });

                    document.getElementById('numInfo').innerText = `Numbers: ${allNumbers.length} (NT ${ntCount} NC ${ncCount})`;
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

                        while (charCount > (459 + (smsCount - 3) * 152)) {
                            smsCount++;
                            remainingChars = Math.abs(charCount - (459 + (smsCount - 3) * 152));
                        }
                    }

                    document.getElementById('charCount').innerText = `Characters: ${charCount} / ${remainingChars}`;
                    document.getElementById('smsCount').innerText = 'SMS Count: ' + smsCount;
                    document.getElementById('rateInfo').innerText = `Rate: NT ${rate} NC ${rate}`;

                    var totalRate = smsCount * ((ntCount * rate) + (ncCount * rate));
                    document.getElementById('totalCost').innerText = `Total Cost (${(ntCount * rate).toFixed(2)} + ${(ncCount * rate).toFixed(2)}): ${(totalRate).toFixed(2)}`;
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
