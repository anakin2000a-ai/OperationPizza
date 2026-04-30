<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Assignments Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ddd;
        }
        th {
            background-color: orange;
            color: white;
        }
    </style>
</head>
<body>
    <!-- ✅ Changed to $storeName -->
    <h1>Cleaning Task Assignments Report - {{ $storeName }}</h1>
    <table>
        <thead>
            <tr>
                <th>Task Name</th>
                <th>Employee</th>
                <th>Assigned At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($taskAssignments as $assignment)
            <tr>
                <td>{{ $assignment->cleaningTask->name }}</td>
                <td>{{ $assignment->employee->FirstName . ' ' . $assignment->employee->LastName }}</td>
                <td>{{ $assignment->assigned_at }}</td>
                <td>{{ $assignment->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>