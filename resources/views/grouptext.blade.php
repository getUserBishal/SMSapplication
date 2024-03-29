
<div class="row">
    <div class="col-sm-12 text-center">
        <h4>Group SMS</h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-secondary alert-dismissible fade show" role="alert">
            Allow Customization??
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
</div>

<div style="background-color: rgb(228, 228, 228); padding: 20px; max-width: 600px; margin: 0 auto;">
    <form method="POST" action="{{ url('group-text') }}">
        {{ csrf_field() }}

        <div class="form-group">
            <label for="groupSelect">Select Group:</label>
            <select class="form-control" id="groupSelect" name="selected_group" onchange="group_fetchGroupNumbers()">
                <option value="">Select a group</option>
                @foreach ($groups as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Message</label>
            <textarea class="form-control" name="message" id="message" rows="1" required oninput="group_updateInfo(this.value)"></textarea>
            <div style="position: relative;">
                <div id="nepaliSuggestionsDropdown" class="group-dropdown-menu" style="position: absolute; top: 100%; left: 0; display: none;"></div>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="nepaliCheckbox" onchange="group_togglegroup_nepaliMode()">
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
            <p id="group_smsCount">SMS Count: 0</p>
            <p id="numInfo">Numbers: 0 (NT 0 NC 0)</p>
            <p id="group_rateInfo">group_rate: NT 0.2 NC 0.2</p>
            <p id="totalCost">Total Cost (0.00 + 0.00 +): 0.00</p>
        </div>

        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Send SMS">
        </div>
    </form>
</div>

<script>
    let group_rate = 0.2;
    let totalgroup_rate = 0;
    let group_smsCount = 0;
    let group_nepaliMode = false;
    let group_selectedNumbers = [];

    function group_togglegroup_nepaliMode() {
        group_nepaliMode = !group_nepaliMode;
        document.getElementById('message').value = '';
        document.getElementById('message').focus();
        group_hideRecommendations();
    }

    function group_updateNumbers() {
        var ntCount = group_selectedNumbers.filter(num => group_getOperatorType(num) === 'NTC').length;
        var ncCount = group_selectedNumbers.filter(num => group_getOperatorType(num) === 'NCELL').length;

        document.getElementById('numInfo').innerText = `Numbers: ${group_selectedNumbers.length} (NT ${ntCount} NC ${ncCount})`;
    }

    function group_getOperatorType(mobil) {
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
    function group_fetchGroupNumbers() {
        const selectedGroup = document.getElementById('groupSelect').value;
        if (selectedGroup !== "") {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `/fetch-group-numbers?group=${selectedGroup}`, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const numbers = JSON.parse(xhr.responseText);
                        document.getElementById('groupNumbers').value = numbers.join(', ');
                        document.getElementById('groupNumbersField').style.display = 'block';
                    } catch (error) {
                        console.error('Error parsing JSON response:', error);
                    }
                } else {
                    console.error('Request failed with status:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Network error occurred.');
            };
            xhr.send();
        } else {
            document.getElementById('groupNumbers').value = '';
            document.getElementById('groupNumbersField').style.display = 'none';
        }
    }


    function group_displayGroupNumbers(numbers) {
        const numbersInput = document.getElementById('groupNumbers');
        numbersInput.value = numbers.join(', ');
        document.getElementById('groupNumbersField').style.display = 'block';
    }

    async function group_updateInfo(text) {
        let charCount = text.length;
        let remainingChars = charCount;
        let group_smsCount = 0;

        if (charCount <= 160) {
            group_smsCount = 1;
            remainingChars = 160 - charCount;
        } else if (charCount <= 306) {
            group_smsCount = 2;
            remainingChars = 306 - charCount;
        } else if (charCount <= 459) {
            group_smsCount = 3;
            remainingChars = 459 - charCount;
        } else {
            group_smsCount = 3;
            remainingChars = 152;

            while (charCount > (459 + (group_smsCount - 3) * 152)) {
                group_smsCount++;
                remainingChars = Math.abs(charCount - (459 + (group_smsCount - 3) * 152));
            }
        }

        document.getElementById('charCount').innerText = `Characters: ${charCount} / ${remainingChars}`;
        document.getElementById('group_smsCount').innerText = 'SMS Count: ' + group_smsCount;
        document.getElementById('group_rateInfo').innerText = `group_rate: NT ${group_rate} NC ${group_rate}`;

        var totalgroup_rate = group_smsCount * group_rate;
        document.getElementById('totalCost').innerText = `Total Cost (${group_rate.toFixed(2)} + ${group_rate.toFixed(2)}): ${(totalgroup_rate).toFixed(2)}`;

        if (group_nepaliMode) {
            const nepaliSuggestions = await group_fetchNepaliSuggestions(text);
            if (nepaliSuggestions && nepaliSuggestions.length > 0) {
                const dropdown = document.getElementById('nepaliSuggestionsDropdown');
                dropdown.innerHTML = '';
                nepaliSuggestions.forEach(suggestion => {
                    const option = document.createElement('div');
                    option.classList.add('dropdown-item');
                    option.textContent = suggestion;
                    option.onclick = () => {
                        document.getElementById('message').value = suggestion;
                        group_hideRecommendations();
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

    async function group_fetchNepaliSuggestions(input) {
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

    function group_hideRecommendations() {
        document.getElementById('nepaliSuggestionsDropdown').innerHTML = '';
        document.getElementById('nepaliSuggestionsDropdown').style.display = 'none';
    }

    function group_selectNumber(phoneNumber) {
        group_selectedNumbers.push(phoneNumber);
        group_updateNumbers();
        document.getElementById('group_selectedNumbersInput').value = group_selectedNumbers.join(', ');
    }

    function group_updateSelectedNumber() {
        const selectedNumber = document.getElementById('phoneNumbersDropdown').value;
        if (!group_selectedNumbers.includes(selectedNumber)) {
            group_selectedNumbers.push(selectedNumber);
            group_updateNumbers();
            document.getElementById('group_selectedNumbersInput').value = group_selectedNumbers.join(', ');
        } else {
            console.log('Number already selected:', selectedNumber);
        }
    }

    function group_selectAll() {
        var checkboxes = document.getElementsByName('selected_phone_number[]');
        var checkAll = document.getElementById('checkAll');

        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = checkAll.checked;
            if (checkAll.checked) {
                group_selectNumber(checkboxes[i].value);
            } else {
                const index = group_selectedNumbers.indexOf(checkboxes[i].value);
                if (index > -1) {
                    group_selectedNumbers.splice(index, 1);
                }
            }
        }

        group_updateNumbers();
        document.getElementById('group_selectedNumbersInput').value = group_selectedNumbers.join(', ');
    }

</script>

<style>
.group-dropdown-menu {
    background-color: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 999;
}

</style>

