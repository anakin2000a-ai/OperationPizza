<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
        }
        h1 { 
            text-align: center; 
            margin-bottom: 5px; 
        }
        .date-range { 
            text-align: center; 
            margin-bottom: 20px; 
            color: #555; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        th, td { 
            border: 1px solid #333; 
            padding: 8px 12px; 
            text-align: left; 
        }
        th { 
            background-color: #e87400; 
            color: white; 
            font-weight: bold; 
        }
        tr:nth-child(even) { 
            background-color: #f9f9f9; 
        }
        td:first-child { 
            vertical-align: middle; 
            text-align: center; 
            font-weight: bold; 
        }
    </style>
</head>
<body>
    <h1>{{ $storeName }} - Position Chart</h1>
    
    <div class="date-range">
        {{ \Carbon\Carbon::parse($masterSchedule->start_date)->format('M d, Y') }} 
        - 
        {{ \Carbon\Carbon::parse($masterSchedule->end_date)->format('M d, Y') }}
    </div>

    @php
        $grouped = [];
        foreach ($positionData as $data) {
            $day = $data['date'];
            if (!isset($grouped[$day])) {
                $grouped[$day] = [];
            }
            $grouped[$day][] = $data;
        }
    @endphp

    <table>
        <thead>
            <tr>
                <th>Day</th>
                <th>Employee Name</th>
                <th>Skill</th>
                <th>Start Time</th>
                <th>End Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grouped as $day => $rows)
                @foreach($rows as $index => $data)
                <tr>
                    @if($index === 0)
                        <td rowspan="{{ count($rows) }}">{{ $day }}</td>
                    @endif
                    <td>{{ $data['employee_name'] }}</td>
                    <td>{{ $data['skill_name'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($data['start_time'])->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($data['end_time'])->format('H:i') }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>