    <div class="row">
        <div class="col-sm-12 text-center">
            <h4>Single SMS</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                Send Hllow only?
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
                <label>Phone Numbers</label>
                <input type="hidden" id="single_selectedNumbersInput" name="selected_phone_number[]" value="">

                <select id="phoneNumbersDropdown" class="form-control" onchange="single_updateSelectedNumber()">
                    <option value="">Select a number</option>
                    @foreach ($contacts as $item)
                        <option value="{{ $item->phone_number }}">{{ $item->phone_number }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>single_message</label>
                <div style="position: relative;">
                    <input type="text" class="form-control" name="single_message" id="single_message" oninput="single_updateInfo(this.value)">
                    <div id="single_nepaliSuggestionsDropdown" class="dropdown-menu" style="position: absolute; top: 100%; left: 0; display: none;"></div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="nepaliCheckbox" onchange="single_togglesingle_nepaliMode()">
                    <label class="form-check-label"  for="nepaliCheckbox">
                        Nepali
                    </label>
                </div>
            </div>

            <div>
                <label>Send?</label>
                <div>
                    <input type="radio" name="salutation" value="Yes" checked> Yes
                    <input type="radio" name="salutation" value="No"> No
                </div>
            </div>

            <div id="infoContainer" style="background-color: rgb(225, 220, 220); padding: 20px; max-width: 600px; margin: 20px auto;">
                <p id="single_charCount">Characters: 0 / 160</p>
                <p id="single_smsCount">SMS Count: 0</p>
                <p id="single_numInfo">Numbers: 0 (NT 0 NC 0)</p>
                <p id="single_rateInfo">single_rate: NT 0.2 NC 0.2</p>
                <p id="single_totalCost">Total Cost (0.00 + 0.00 +): 0.00</p>
            </div>

            <div>
                <input type="submit" class="btn btn-primary" value="Send SMS">
            </div>
        </form>

        <script>
            let single_rate = 0.2;
            let totalsingle_rate = 0;
            let single_smsCount = 0;
            let single_nepaliMode = false;
            let single_selectedNumbers = [];

            function single_togglesingle_nepaliMode() {
                single_nepaliMode = !single_nepaliMode;
                document.getElementById('single_message').value = '';
                document.getElementById('single_message').focus();
                single_hideRecommendations();
            }

            function single_updateNumbers() {
                var ntCount = single_selectedNumbers.filter(num => single_getOperatorType(num) === 'NTC').length;
                var ncCount = single_selectedNumbers.filter(num => single_getOperatorType(num) === 'NCELL').length;

                document.getElementById('single_numInfo').innerText = `Numbers: ${single_selectedNumbers.length} (NT ${ntCount} NC ${ncCount})`;
            }

            function single_getOperatorType(mobil) {
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

            async function single_updateInfo(text) {
                let single_charCount = text.length;
                let remainingChars = single_charCount;
                let single_smsCount = 0;

                if (single_charCount <= 160) {
                    single_smsCount = 1;
                    remainingChars = 160 - single_charCount;
                } else if (single_charCount <= 306) {
                    single_smsCount = 2;
                    remainingChars = 306 - single_charCount;
                } else if (single_charCount <= 459) {
                    single_smsCount = 3;
                    remainingChars = 459 - single_charCount;
                } else {
                    single_smsCount = 3;
                    remainingChars = 152;

                    while (single_charCount > (459 + (single_smsCount - 3) * 152)) {
                        single_smsCount++;
                        remainingChars = Math.abs(single_charCount - (459 + (single_smsCount - 3) * 152));
                    }
                }

                document.getElementById('single_charCount').innerText = `Characters: ${single_charCount} / ${remainingChars}`;
                document.getElementById('single_smsCount').innerText = 'SMS Count: ' + single_smsCount;
                document.getElementById('single_rateInfo').innerText = `single_rate: NT ${single_rate} NC ${single_rate}`;

                var totalsingle_rate = single_smsCount * single_rate;
                document.getElementById('single_totalCost').innerText = `Total Cost (${single_rate.toFixed(2)} + ${single_rate.toFixed(2)}): ${(totalsingle_rate).toFixed(2)}`;

                if (single_nepaliMode) {
                    const nepaliSuggestions = await single_fetchNepaliSuggestions(text);
                    if (nepaliSuggestions && nepaliSuggestions.length > 0) {
                        const dropdown = document.getElementById('single_nepaliSuggestionsDropdown');
                        dropdown.innerHTML = '';
                        nepaliSuggestions.forEach(suggestion => {
                            const option = document.createElement('div');
                            option.classList.add('dropdown-item');
                            option.textContent = suggestion;
                            option.onclick = () => {
                                document.getElementById('single_message').value = suggestion;
                                single_hideRecommendations();
                            };
                            dropdown.appendChild(option);
                        });
                        dropdown.style.display = 'block';
                    } else {
                        document.getElementById('single_nepaliSuggestionsDropdown').style.display = 'none';
                    }
                } else {
                    document.getElementById('single_nepaliSuggestionsDropdown').style.display = 'none';
                }
            }

            async function single_fetchNepaliSuggestions(input) {
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

            function single_hideRecommendations() {
                document.getElementById('single_nepaliSuggestionsDropdown').innerHTML = '';
                document.getElementById('single_nepaliSuggestionsDropdown').style.display = 'none';
            }

            function single_selectNumber(phoneNumber) {
                single_selectedNumbers.push(phoneNumber);
                single_updateNumbers();
                document.getElementById('single_selectedNumbersInput').value = single_selectedNumbers.join(', ');
            }

            function single_updateSelectedNumber() {
                const selectedNumber = document.getElementById('phoneNumbersDropdown').value;
                document.getElementById('single_selectedNumbersInput').value = selectedNumber;
            }


            function single_addManualNumber() {
                const enteredNumber = document.getElementById('manualPhoneNumber').value.trim();
                if (enteredNumber !== '') {
                    single_selectNumber(enteredNumber);
                    document.getElementById('manualPhoneNumber').value = '';
                }
            }

            function single_selectAll() {
                var checkboxes = document.getElementsByName('selected_phone_number[]');
                var checkAll = document.getElementById('checkAll');

                for (var i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = checkAll.checked;
                    if (checkAll.checked) {
                        single_selectNumber(checkboxes[i].value);
                    } else {
                        const index = single_selectedNumbers.indexOf(checkboxes[i].value);
                        if (index > -1) {
                            single_selectedNumbers.splice(index, 1);
                        }
                    }
                }

                single_updateNumbers();
                document.getElementById('single_selectedNumbersInput').value = single_selectedNumbers.join(', ');
            }

        </script>

<style>
    .single-dropdown-menu {
    background-color: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 999;
}

</style>


