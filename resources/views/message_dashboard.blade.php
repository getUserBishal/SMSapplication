<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        .dashboard {
            display: flex;
            flex-direction: row;
        }
        .section {
            flex: 1;
            margin: 10px;
            border: 1px solid #ccc;
            padding: 10px;
        }
    </style>
</head>
<body>
    {{-- @include() --}}
    <div class="dashboard">
        <div class="section">
            @if (!isset($includedGroup))
                @include('grouptext')
                @php $includedGroup = true; @endphp
            @endif
        </div>
        <div class="section">
            @if (!isset($includedSingle))
                @include('singletext')
                @php $includedSingle = true; @endphp
            @endif
        </div>
        <div class="section">
            @if (!isset($includedContact))
                @include('contacttext')
                @php $includedContact = true; @endphp
            @endif
        </div>
    </div>
</body>
</html>
