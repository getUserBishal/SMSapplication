
    <div class="row">
        <div class="col-sm-12">
            <h4>Bulk SMS</h4>
        </div>
    </div>
    <div class="alert alert-secondary">
        The number pasted should be in sepabulk_rate lines.
    </div>
    <div style="background-color: rgb(228, 228, 228); padding: 20px; max-width: 600px; margin: 0 auto;">
        <form action="{{ url('single-text') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div>
                <div class="form-group">
                    <label>Phone Numbers</label>
                    <input type="file" class="form-control-file" name="phone_numbers_file" id="phoneNumbersFile" accept=".txt,.csv,.xlsx,.xls" onchange="bulk_showbulk_selectedNumbers(); bulk_updateNumbers()">
                    <small class="form-text text-muted">Accepted formats: .txt, .csv, .xlsx, .xls</small>
                    <textarea class="form-control" id="phoneNumbersInput" name="phone_numbers" rows="5" placeholder="Enter or upload phone numbers" required oninput="bulk_updateNumbers()"></textarea>
                </div>
            </div>
            <div>
                <div>
                    <label>bulk_message</label>
                    <div style="position: relative;">
                        <input type="text" class="form-control" name="bulk_message" id="bulk_message" oninput="bulk_updateInfo(this.value)">
                        <div id="nepaliSuggestionsDropdown" class="bulk-dropdown-menu" style="position: absolute; top: 100%; left: 0; display: none;"></div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="nepaliCheckbox" onchange="bulk_togglebulk_nepaliMode()">
                        <label class="form-check-label" for="nepaliCheckbox">
                            Nepali
                        </label>
                    </div>
                </div>
            </div>

            <div id="infoContainer" style="background-color: rgb(225, 220, 220); padding: 20px; max-width: 600px; margin: 20px auto;">
                <p id="charCount">Characters: 0 / 160</p>
                <p id="bulk_smsCount">SMS Count: 0</p>
                <p id="numInfo">Numbers: 0 (NT 0 NC 0)</p>
                <p id="bulk_rateInfo">bulk_rate: NT 0.2 NC 0.2</p>
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
        let bulk_rate = 0.2;
        let totalbulk_rate = 0;
        let bulk_smsCount = 0;
        let bulk_nepaliMode = false;
        let bulk_selectedNumbers = [];

        function bulk_togglebulk_nepaliMode() {
            bulk_nepaliMode = !bulk_nepaliMode;
            document.getElementById('bulk_message').value = '';
            document.getElementById('bulk_message').focus();
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

        function bulk_showbulk_selectedNumbers() {
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
                let bulk_smsCount = 0;

            if (charCount <= 160) {
                bulk_smsCount = 1;
                remainingChars = 160 - charCount;
            } else if (charCount <= 306) {
                bulk_smsCount = 2;
                remainingChars = 306 - charCount;
            } else if (charCount <= 459) {
                bulk_smsCount = 3;
                remainingChars = 459 - charCount;
            } else {
                bulk_smsCount = 3;
                remainingChars = 152;
                while (charCount > (459 + (bulk_smsCount - 3) * 152)) {
                    bulk_smsCount++;
                    remainingChars = Math.abs(charCount - (459 + (bulk_smsCount - 3) * 152));
                }
            }
            document.getElementById('charCount').innerText = `Characters: ${charCount} / ${remainingChars}`;
            document.getElementById('bulk_smsCount').innerText = 'SMS Count: ' + bulk_smsCount;
            document.getElementById('bulk_rateInfo').innerText = `bulk_rate: NT ${bulk_rate} NC ${bulk_rate}`;

            var totalbulk_rate = bulk_smsCount * bulk_rate;
            document.getElementById('totalCost').innerText = `Total Cost (${bulk_rate.toFixed(2)} + ${bulk_rate.toFixed(2)}): ${(totalbulk_rate).toFixed(2)}`;

            if (bulk_nepaliMode) {
                const nepaliSuggestions = await bulk_fetchNepaliSuggestions(text);
                if (nepaliSuggestions && nepaliSuggestions.length > 0) {
                    const dropdown = document.getElementById('nepaliSuggestionsDropdown');
                    dropdown.innerHTML = '';
                    nepaliSuggestions.forEach(suggestion => {
                        const option = document.createElement('div');
                        option.classList.add('dropdown-item');
                        option.textContent = suggestion;
                        option.onclick = () => {
                            document.getElementById('bulk_message').value = suggestion;
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
            bulk_selectedNumbers.push(phoneNumber);
            bulk_updateNumbers();
            document.getElementById('bulk_selectedNumbersInput').value = bulk_selectedNumbers.join(', ');
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
