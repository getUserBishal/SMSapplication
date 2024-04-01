<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Sociar SMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css"/>
    <style>
        html,
        body {
            height: 100%;
        }
        .container-fluid {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding-left: 0;
            padding-right: 0;
        }
        .row {
            flex-grow: 1;
            margin-right: 0;
            margin-left: 0;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            padding-top: 1rem;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            overflow-y: auto;
        }
        .navbar {
            width: 250px;
            background-color: #343a40;
            min-height: 100%;
            position: fixed;
            top: 0;
            left: 0;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link.active {
            background-color: #495057;
        }
        .navbar-brand {
            padding: 0.5rem 1rem;
            margin-right: 0;
            font-size: 1.25rem;
            line-height: inherit;
            white-space: nowrap;
            border: 0;
        }
    </style>
</head>
<body>

    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js" > </script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="{{url('dashboard')}}">Sociar SMS</a>
    </nav>
    <div class="row">
        <nav class="col-md-2 sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{url('dashboard')}}">Outbox</a>
                    </li>
                    {{-- <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="sendSMSDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Send SMS</a>
                        <div class="dropdown-menu" aria-labelledby="sendSMSDropdown">
                            <a class="dropdown-item" href="{{url('single-text')}}">Bulk SMS</a>
                            <a class="dropdown-item" href="{{url('contacts-text')}}">Single SMS</a>
                            <a class="dropdown-item" href="{{url('group-text')}}">Group SMS</a>
                        </div>
                    </li> --}}

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="sendToDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Contacts</a>
                        <div class="dropdown-menu" aria-labelledby="sendToDropdown">
                            <a class="dropdown-item" href="{{url('contacts')}}">Add Contact's</a>
                            <a class="dropdown-item" href="{{url('contacts-group')}}">Add Contact Group's</a>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="deliveryStatusDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Delivery Status</a>
                        <div class="dropdown-menu" aria-labelledby="deliveryStatusDropdown">
                            <a class="dropdown-item" href="{{url('delivery-report')}}">Delivery Reports</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <main role="main" class="col-md-10 main-content">
            <div class="text-center mt-5">
                <div class="row">
                    <div class="col-sm-12">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                        @if (session('error_status'))
                            <div class="alert alert-success">
                                {{ session('error_status') }}
                            </div>
                        @endif
                    </div>

                </div>
                <h1 style="background-color: #495057">Sociar SMS</h1>
                {{-- @yield('content') --}}

                @include('message_dashboard')
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#example').DataTable({
            "pageLength": 50
        });
    });
</script>

<script>
    $("#checkAll").click(function(){
        $('input:checkbox').not(this).prop('checked', this.checked);
    });
</script>

</body>
</html>
