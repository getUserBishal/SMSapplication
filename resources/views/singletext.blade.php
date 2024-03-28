{{-- @extends('base')

@section('content') --}}
    <div class="row">
        <div class="col-sm-12">
            <h4>Bulk SMS</h4>
        </div>
    </div>
    <div class="alert alert-secondary">
        The number pasted should be in separate lines.
    </div>
    <div style="background-color: rgb(228, 228, 228); padding: 20px; max-width: 600px; margin: 0 auto;">
        <form action="{{ url('single-text') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div>
                <div class="form-group">
                    <label>Phone Numbers</label>
                    <input type="file" class="form-control-file" name="phone_numbers_file" id="phoneNumbersFile" accept=".txt,.csv,.xlsx,.xls" onchange="bulk_showSelectedNumbers(); bulk_updateNumbers()">
                    <small class="form-text text-muted">Accepted formats: .txt, .csv, .xlsx, .xls</small>
                    <textarea class="form-control" id="phoneNumbersInput" name="phone_numbers" rows="5" placeholder="Enter or upload phone numbers" required oninput="bulk_updateNumbers()"></textarea>
                </div>
            </div>
            <div>
                <div>
                    <label>Message</label>
                    <div style="position: relative;">
                        <input type="text" class="form-control" name="message" id="message" oninput="bulk_updateInfo(this.value)">
                        <div id="nepaliSuggestionsDropdown" class="bulk-dropdown-menu" style="position: absolute; top: 100%; left: 0; display: none;"></div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="nepaliCheckbox" onchange="bulk_toggleNepaliMode()">
                        <label class="form-check-label" for="nepaliCheckbox">
                            Nepali
                        </label>
                    </div>
                </div>
            </div>

            <div id="infoContainer" style="background-color: rgb(225, 220, 220); padding: 20px; max-width: 600px; margin: 20px auto;">
                <p id="charCount">Characters: 0 / 160</p>
                <p id="smsCount">SMS Count: 0</p>
                <p id="numInfo">Numbers: 0 (NT 0 NC 0)</p>
                <p id="rateInfo">Rate: NT 0.2 NC 0.2</p>
                <p id="totalCost">Total Cost (0.00 + 0.00): 0.00</p>
            </div>
            <div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Send SMS">
                </div>
            </div>
        </form>
    </div>

    <script>
        let rate = 0.2;
        let totalRate = 0;
        let smsCount = 0;
        let nepaliMode = false;
        let selectedNumbers = [];

        function bulk_toggleNepaliMode() {
            nepaliMode = !nepaliMode;
            document.getElementById('message').value = '';
            document.getElementById('message').focus();
            bulk_hideRecommendations();
        }

        function bulk_updateNumbers() {
            var phoneNumbersInput = document.getElementById('phoneNumbersInput');
            var allNumbers = phoneNumbersInput.value.split('\n');
            ntCount = 0;
            ncCount = 0;
            allNumbers.forEach(function(number) {
                var operatorType = bulk_getOperatorType(number);
                if (operatorType === 'NTC') {
                    ntCount++;
                } else if (operatorType === 'NCELL') {
                    ncCount++;
                }
            });
            document.getElementById('numInfo').innerText = `Numbers: ${allNumbers.length} (NT ${ntCount} NC ${ncCount})`;
        }

        function bulk_getOperatorType(mobil) {
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

        function bulk_showSelectedNumbers() {
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
                    var existingNumbers = phoneNumbersInput.value.trim();
                    if (existingNumbers !== '') {
                        existingNumbers += '\n';
                    }
                    phoneNumbersInput.value = existingNumbers + numbers.join('\n');
                    bulk_updateNumbers();
                };
                reader.readAsText(selectedFile);
            }
        }
        async function bulk_updateInfo(text) {
            let charCount = text.length;
                let remainingChars = charCount;
                let smsCount = 0;

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

            var totalRate = smsCount * rate;
            document.getElementById('totalCost').innerText = `Total Cost (${rate.toFixed(2)} + ${rate.toFixed(2)}): ${(totalRate).toFixed(2)}`;

            if (nepaliMode) {
                const nepaliSuggestions = await bulk_fetchNepaliSuggestions(text);
                if (nepaliSuggestions && nepaliSuggestions.length > 0) {
                    const dropdown = document.getElementById('nepaliSuggestionsDropdown');
                    dropdown.innerHTML = '';
                    nepaliSuggestions.forEach(suggestion => {
                        const option = document.createElement('div');
                        option.classList.add('dropdown-item');
                        option.textContent = suggestion;
                        option.onclick = () => {
                            document.getElementById('message').value = suggestion;
                            bulk_hideRecommendations();
                        };
                        dropdown.appendChild(option);
                    });
                    dropdown.style.display = 'block';
                } else {
                    document.getElementById('nepaliSuggestionsDropdown').style.display = 'none';
                }
            } else {
                document.getElementById('nepaliSuggestionsDropdown').style.display = 'none';
            }
        }


        async function bulk_fetchNepaliSuggestions(input) {
            const url = `https://inputtools.google.com/request?text=${input}&itc=ne-t-i0-und&num=13&cp=0&cs=1&ie=utf-8&oe=utf-8`;

            try {
                const response = await fetch(url);
                const data = await response.json();

                if (data && data.length > 1 && data[1].length > 0) {
                    const suggestionsArray = data[1][0][1];
                    return suggestionsArray;
                }
            } catch (error) {
                console.error('Error fetching Nepali suggestions:', error);
            }
            return [];
        }

        function bulk_hideRecommendations() {
            document.getElementById('nepaliSuggestionsDropdown').innerHTML = '';
            document.getElementById('nepaliSuggestionsDropdown').style.display = 'none';
        }

        function bulk_selectNumber(phoneNumber) {
            selectedNumbers.push(phoneNumber);
            bulk_updateNumbers();
            document.getElementById('selectedNumbersInput').value = selectedNumbers.join(', ');
        }

    </script>

<style>
.bulk-dropdown-menu {
background-color: white;
border: 1px solid #ccc;
border-radius: 4px;
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
max-height: 200px;
overflow-y: auto;
z-index: 999;
}

</style>
<script>
    $(document).ready(function() {
        $('#example').DataTable();
    });
</script>
{{-- @endsection --}}
</body>
</html>
