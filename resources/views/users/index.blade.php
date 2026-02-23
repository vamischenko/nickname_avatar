<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Users</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f5f5f5; padding: 2rem; }
        h1 { margin-bottom: 1.5rem; color: #333; }
        .users { display: flex; flex-wrap: wrap; gap: 1rem; }
        .user-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            width: 180px;
            text-align: center;
        }
        .user-card img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 0.5rem;
        }
        .user-card .nickname { font-weight: bold; color: #333; word-break: break-all; }
        .user-card .date { font-size: 0.75rem; color: #999; margin-top: 0.25rem; }
        .empty { color: #888; font-style: italic; }
    </style>
</head>
<body>
    <h1>Registered Users ({{ count($users) }})</h1>

    @if(empty($users))
        <p class="empty">No users registered yet.</p>
    @else
        <div class="users">
            @foreach($users as $user)
                <div class="user-card">
                    <img src="{{ asset('storage/' . $user['avatar']) }}" alt="{{ $user['nickname'] }}">
                    <div class="nickname">{{ $user['nickname'] }}</div>
                    <div class="date">{{ $user['created_at'] }}</div>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
