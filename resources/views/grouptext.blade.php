<div class="row">
    <div class="col-sm-12 text-center">
        <h4>Group SMS</h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-secondary alert-dismissible fade show" role="alert">
            Send Hello??
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
            <input type="submit" class="btn btn-primary" value="Send SMS" onclick="return sendGroupSMS();">
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

    console.log('Selected Numbers:', group_selectedNumbers);
    console.log('NTC Count:', ntCount);
    console.log('NCELL Count:', ncCount);

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

    if (selectedGroup) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `/fetch-group-numbers?group=${encodeURIComponent(selectedGroup)}`, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const groupNumbers = JSON.parse(xhr.responseText);
                    console.log('Received group numbers:', groupNumbers);
                    group_selectedNumbers = [];
                    groupNumbers.forEach(number => {
                        if (group_getOperatorType(number) !== '') {
                            group_selectNumber(number);
                        }
                    });
                    group_updateNumbers();
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
        group_selectedNumbers = [];
        group_updateNumbers();
    }
}


function group_selectNumber(phoneNumber) {
    if (!group_selectedNumbers.includes(phoneNumber)) {
        group_selectedNumbers.push(phoneNumber);
        group_updateNumbers();
    }
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

        totalgroup_rate = group_smsCount * group_rate * group_selectedNumbers.length;
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

    function sendGroupSMS() {
        event.preventDefault();
        const message = document.getElementById('message').value;
        const mobileNumbers = group_selectedNumbers.join(',');

        if (message && mobileNumbers) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'https://sms.sociair.com/api/sms', true);
            xhr.setRequestHeader('Authorization', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyIiwianRpIjoiZDJhYWY4MzU3MDQ2ODhhY2JlNjRhZmM0YmQzYmExODdjZjhiMGJlYTcxZTZiYzRmZTE3YmQ2ZTU2MzU0NDYyNDFmYWRkODZkMDhhODY3OWIiLCJpYXQiOjE3MTIwMzUzODcuODA3OTgsIm5iZiI6MTcxMjAzNTM4Ny44MDc5ODYsImV4cCI6MTc0MzU3MTM4Ny43ODA5OTcsInN1YiI6IjEyNTciLCJzY29wZXMiOltdfQ.eJS_NUDVvuTrheHlcd8t8Sronp6DMTd2FC5KAWZBOwzCLMAbxQdwlYNFgRshsea9CB-bC3O1ORIJ0_SdPc3n7LtyiNb1chqGBRqJ018HUxU2ljl8GbKKzGo_zNsr9UuRKp4oEw5t40dPXCgmpKwaxooHfwx75p9YjOU072wO6KhAYl-I0sl5WIIcyOJuqxZiBqT3nnTYaFzitpKU3sAX0NEXT4L5wbrZt-mDbyUatifWVBS3VpjdBfTDPz4yH6y_2NoiNwePVhnqIUba0YykPAbALQdvP5bfPkAi3GoxoTCsagUR-Dcvk40WNd1I_vRO2YAdzwr9-9Cl-UFzo8E9Y1EnxWIUeR5mXb5l6iGVQ5bHxqtpQsTU-9WvN-1w1dzebZAAqJ6QD0DR2tPCZ4ZEDnXZK6KDPV4gWsscaieR3hMiJ84ct0VfuUnp18yC1VmVTd9_1F-YOpCEdBtGCo7TSK1kxGkNQwq3FCIAEeatx1lsbP-e9nWrEEP3jZklgEohF_W8wvyY5hEzXVQY1qwh7Z47XVxtwE6eFG3QTdo4BRtp8ccMFqY9l5JZQXdxFOANsngqwcDFmt-DDzCwev-EcXtCSBscOOstjh7lk6IWCdWP5qqelHV7RR9QsgFwUazEZoKW33yjLNjCbQ0QN2jJEEHCAfRXjzr4gGbfmVN0V6M');
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        console.log('SMS sent successfully:', response);

                    } catch (error) {
                        console.error('Error parsing response:', error);
                    }
                } else {
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        console.error('SMS sending failed:', errorResponse);
                    } catch (error) {
                        console.error('Error parsing response:', error);
                    }
                }
            };

            xhr.onerror = function() {
                console.error('Network error occurred.');
            };

            const requestData = {
                message: message,
                mobile: mobileNumbers
            };

            xhr.send(JSON.stringify(requestData));
        } else {
            console.error('Message or mobile numbers are missing.');
        }
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
