
    <div class="row">
        <div class="col-sm-12 text-center">
            <h4>Single SMS</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                Click on salutation for custom message
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
    <div style="background-color: rgb(228, 228, 228); padding: 20px; max-width: 600px; margin: 0 auto;">
        <form method="POST" action="{{ url('group-text') }}">
            {{ csrf_field() }}

            <div>
                <label>Phone Numbers</label>
                <select id="phoneNumbersDropdown" class="form-control" onchange="single_updateSelectedNumber()">
                    <option value="">Select a number</option>
                    @foreach ($contacts as $item)
                        <option value="{{ $item->phone_number }}">{{ $item->phone_number }}</option>
                    @endforeach
                </select>
                <input type="hidden" id="selectedNumbersInput" name="selected_phone_number[]" value="">

            </div>

            <div>
                <label>Message</label>
                <div style="position: relative;">
                    <input type="text" class="form-control" name="message" id="message" oninput="single_updateInfo(this.value)">
                    <div id="nepaliSuggestionsDropdown" class="dropdown-menu" style="position: absolute; top: 100%; left: 0; display: none;"></div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="nepaliCheckbox" onchange="single_toggleNepaliMode()">
                    <label class="form-check-label" for="nepaliCheckbox">
                        Nepali
                    </label>
                </div>
            </div>

            <div>
                <label>Include customization?</label>
                <div>
                    <input type="radio" name="salutation" value="Yes" checked> Yes
                    <input type="radio" name="salutation" value="No"> No
                </div>
            </div>

            <div id="infoContainer" style="background-color: rgb(225, 220, 220); padding: 20px; max-width: 600px; margin: 20px auto;">
                <p id="charCount">Characters: 0 / 160</p>
                <p id="smsCount">SMS Count: 0</p>
                <p id="numInfo">Numbers: 0 (NT 0 NC 0)</p>
                <p id="rateInfo">Rate: NT 0.2 NC 0.2</p>
                <p id="totalCost">Total Cost (0.00 + 0.00 +): 0.00</p>
            </div>

            <div>
                <input type="submit" class="btn btn-primary" value="Send SMS">
            </div>
        </form>

        <script>
            let rate = 0.2;
            let totalRate = 0;
            let smsCount = 0;
            let nepaliMode = false;
            let selectedNumbers = [];

            function single_toggleNepaliMode() {
                nepaliMode = !nepaliMode;
                document.getElementById('message').value = '';
                document.getElementById('message').focus();
                single_hideRecommendations();
            }

            function single_updateNumbers() {
                var ntCount = selectedNumbers.filter(num => single_getOperatorType(num) === 'NTC').length;
                var ncCount = selectedNumbers.filter(num => single_getOperatorType(num) === 'NCELL').length;

                document.getElementById('numInfo').innerText = `Numbers: ${selectedNumbers.length} (NT ${ntCount} NC ${ncCount})`;
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
                    const nepaliSuggestions = await single_fetchNepaliSuggestions(text);
                    if (nepaliSuggestions && nepaliSuggestions.length > 0) {
                        const dropdown = document.getElementById('nepaliSuggestionsDropdown');
                        dropdown.innerHTML = '';
                        nepaliSuggestions.forEach(suggestion => {
                            const option = document.createElement('div');
                            option.classList.add('dropdown-item');
                            option.textContent = suggestion;
                            option.onclick = () => {
                                document.getElementById('message').value = suggestion;
                                single_hideRecommendations();
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
                document.getElementById('nepaliSuggestionsDropdown').innerHTML = '';
                document.getElementById('nepaliSuggestionsDropdown').style.display = 'none';
            }

            function single_selectNumber(phoneNumber) {
                selectedNumbers.push(phoneNumber);
                single_updateNumbers();
                document.getElementById('selectedNumbersInput').value = selectedNumbers.join(', ');
            }

            function single_updateSelectedNumber() {
                const selectedNumber = document.getElementById('phoneNumbersDropdown').value;
                if (!selectedNumbers.includes(selectedNumber)) {
                    selectedNumbers.push(selectedNumber);
                    single_updateNumbers();
                    document.getElementById('selectedNumbersInput').value = selectedNumbers.join(', ');
                } else {
                    console.log('Number already selected:', selectedNumber);
                }
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
                        const index = selectedNumbers.indexOf(checkboxes[i].value);
                        if (index > -1) {
                            selectedNumbers.splice(index, 1);
                        }
                    }
                }

                single_updateNumbers();
                document.getElementById('selectedNumbersInput').value = selectedNumbers.join(', ');
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

<script>
    $(document).ready(function() {
    $('#example').DataTable();
} );

$(document).ready(function() {
    $('.js-example-basic-multiple').select2();
});
</script>

