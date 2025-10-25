<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translation Analytics Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 16px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-card .label {
            color: #999;
            font-size: 12px;
        }

        .stat-card.success .value {
            color: #10b981;
        }

        .stat-card.warning .value {
            color: #f59e0b;
        }

        .stat-card.info .value {
            color: #3b82f6;
        }

        .chart-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .chart-container h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .latency-table {
            width: 100%;
            border-collapse: collapse;
        }

        .latency-table th,
        .latency-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .latency-table th {
            background: #f9fafb;
            color: #666;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .latency-table tr:hover {
            background: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge.fast {
            background: #d1fae5;
            color: #065f46;
        }

        .badge.medium {
            background: #fef3c7;
            color: #92400e;
        }

        .badge.slow {
            background: #fee2e2;
            color: #991b1b;
        }

        .translations-log {
            max-height: 400px;
            overflow-y: auto;
        }

        .translation-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .translation-item:last-child {
            border-bottom: none;
        }

        .translation-item .text {
            color: #333;
            margin-bottom: 5px;
        }

        .translation-item .meta {
            color: #999;
            font-size: 12px;
        }

        .refresh-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .refresh-btn:hover {
            background: #5568d3;
        }

        .cache-efficiency {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .efficiency-bar {
            flex: 1;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .efficiency-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌐 Translation Analytics Dashboard</h1>
            <p>Monitor your translation service performance and cache efficiency</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card success">
                <h3>Cache Hits</h3>
                <div class="value">{{ $analytics['cache_hits'] ?? 0 }}</div>
                <div class="label">Successful cache retrievals</div>
            </div>

            <div class="stat-card warning">
                <h3>Cache Misses</h3>
                <div class="value">{{ $analytics['cache_misses'] ?? 0 }}</div>
                <div class="label">API calls made</div>
            </div>

            <div class="stat-card info">
                <h3>Cache Hit Rate</h3>
                <div class="value">{{ $hitRate }}%</div>
                <div class="label">Efficiency ratio</div>
                <div class="cache-efficiency">
                    <div class="efficiency-bar">
                        <div class="efficiency-fill" style="width: {{ $hitRate }}%"></div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <h3>Total Requests</h3>
                <div class="value">{{ $totalRequests }}</div>
                <div class="label">All translation requests</div>
            </div>
        </div>

        @if(isset($analytics['latency']) && !empty($analytics['latency']))
        <div class="chart-container">
            <h2>⚡ API Latency by Service</h2>
            <table class="latency-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Requests</th>
                        <th>Avg Latency</th>
                        <th>Min</th>
                        <th>Max</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analytics['latency'] as $service => $data)
                    <tr>
                        <td><strong>{{ ucfirst($service) }}</strong></td>
                        <td>{{ $data['count'] }}</td>
                        <td>{{ number_format($data['avg'], 2) }}ms</td>
                        <td>{{ number_format($data['min'], 2) }}ms</td>
                        <td>{{ number_format($data['max'], 2) }}ms</td>
                        <td>
                            @if($data['avg'] < 200)
                                <span class="badge fast">Fast</span>
                            @elseif($data['avg'] < 500)
                                <span class="badge medium">Medium</span>
                            @else
                                <span class="badge slow">Slow</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($analytics['translations']) && !empty($analytics['translations']))
        <div class="chart-container">
            <h2>📝 Recent Translations</h2>
            <div class="translations-log">
                @foreach(array_reverse(array_slice($analytics['translations'], -20)) as $translation)
                <div class="translation-item">
                    <div class="text">
                        <strong>→</strong> {{ $translation['translation'] }}
                    </div>
                    <div class="meta">
                        Source: "{{ $translation['source'] }}" | 
                        Target: {{ strtoupper($translation['target_lang']) }} | 
                        Service: {{ ucfirst($translation['service']) }} | 
                        Time: {{ $translation['timestamp'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div style="text-align: center; margin-top: 30px;">
            <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Dashboard</button>
        </div>
    </div>
</body>
</html>
