@extends('layouts.app')

@section('title', 'Thống kê Check-in')

@push('styles')
<style>
    .filter-bar { display: flex; gap: 12px; margin-bottom: 24px; align-items: center; flex-wrap: wrap; }
    .filter-bar input[type=date] { padding: 8px 14px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 0.9rem; }
    .progress-bar { height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden; margin-top: 6px; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); border-radius: 4px; transition: width 0.5s; }
    .guest-status { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; }
    .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .dot-in { background: #28a745; }
    .dot-out { background: #ffc107; }
    .dot-none { background: #dee2e6; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .tabs { display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 2px solid #e9ecef; }
    .tab-btn {
        padding: 10px 20px; border: none; background: none; cursor: pointer;
        font-size: 0.9rem; color: #666; border-bottom: 2px solid transparent; margin-bottom: -2px;
        font-weight: 500; transition: all 0.2s;
    }
    .tab-btn.active { color: #667eea; border-bottom-color: #667eea; }
    .timeline-item { display: flex; gap: 16px; padding: 12px 0; border-bottom: 1px solid #f5f5f5; }
    .timeline-time { min-width: 80px; font-size: 0.85rem; color: #888; font-weight: 500; }
    .timeline-content { flex: 1; }
    .timeline-name { font-weight: 600; margin-bottom: 2px; }
    .timeline-action { font-size: 0.8rem; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="filter-bar">
        <h2 style="font-size:1.3rem; font-weight:700;"><i class="fas fa-chart-bar" style="color:#667eea;"></i> Thống kê Check-in</h2>
        <form method="GET" action="{{ route('stats') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <input type="date" name="date" value="{{ $date }}">
            <select name="station_id" class="form-control" style="width:auto;">
                <option value="">Tất cả stations</option>
                @foreach($stations as $st)
                    <option value="{{ $st->id }}" {{ $stationId == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Lọc</button>
        </form>
        <span style="color:#888; font-size:0.85rem;">
            Ngày: <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong>
            @if($stationId) &bull; Station: <strong>{{ $stations->firstWhere('id', $stationId)?->name }}</strong> @endif
        </span>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid">
        <div class="stat-card" style="border-top-color:#667eea;">
            <div class="value" style="color:#667eea;">{{ $totalGuests }}</div>
            <div class="label">Tổng khách trong danh sách</div>
        </div>
        <div class="stat-card" style="border-top-color:#28a745;">
            <div class="value" style="color:#28a745;">{{ $checkedInToday }}</div>
            <div class="label">Đã Check-in hôm nay</div>
            <div class="progress-bar"><div class="progress-fill" style="width:{{ $totalGuests > 0 ? ($checkedInToday/$totalGuests*100) : 0 }}%"></div></div>
        </div>
        <div class="stat-card" style="border-top-color:#ffc107;">
            <div class="value" style="color:#ffc107;">{{ $checkedOutToday }}</div>
            <div class="label">Đã Check-out</div>
        </div>
        <div class="stat-card" style="border-top-color:#17a2b8;">
            <div class="value" style="color:#17a2b8;">{{ max(0, $stillInside) }}</div>
            <div class="label">Đang trong sự kiện</div>
        </div>
        <div class="stat-card" style="border-top-color:#dc3545;">
            <div class="value" style="color:#dc3545;">{{ $totalGuests - $checkedInToday }}</div>
            <div class="label">Chưa check-in</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('tabAll', this)"><i class="fas fa-list"></i> Toàn bộ danh sách</button>
                <button class="tab-btn" onclick="switchTab('tabTimeline', this)"><i class="fas fa-clock"></i> Timeline</button>
            </div>

            {{-- Tab 1: All guests --}}
            <div class="tab-content active" id="tabAll">
                <input type="text" class="form-control" id="searchGuest" placeholder="Tìm kiếm tên khách..." style="margin-bottom:16px; max-width:400px;">
                <div style="overflow-x:auto;">
                    <table class="table" id="guestStatsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tên khách</th>
                                <th>Email</th>
                                <th>Trạng thái</th>
                                <th>Giờ Check-in</th>
                                <th>Giờ Check-out</th>
                                <th>Số lần</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($guests as $guest)
                            @php
                                $todayCheckins = $guest->checkins->filter(fn($c) => $c->checkin_at && $c->checkin_at->toDateString() === $date);
                                $lastCheckin = $todayCheckins->first();
                                $hasCheckin = $todayCheckins->count() > 0;
                                $hasCheckout = $todayCheckins->filter(fn($c) => $c->checkout_at)->count() > 0;
                            @endphp
                            <tr class="guest-stat-row" data-name="{{ strtolower($guest->name) }}">
                                <td>{{ $guest->id }}</td>
                                <td style="font-weight:600;">{{ $guest->name }}</td>
                                <td style="font-size:0.85rem; color:#888;">{{ $guest->email ?? '—' }}</td>
                                <td>
                                    @if($hasCheckin && !$hasCheckout)
                                        <span class="guest-status"><span class="dot dot-in"></span> Đang trong sự kiện</span>
                                    @elseif($hasCheckin && $hasCheckout)
                                        <span class="guest-status"><span class="dot dot-out"></span> Đã check-out</span>
                                    @else
                                        <span class="guest-status"><span class="dot dot-none"></span> Chưa đến</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lastCheckin && $lastCheckin->checkin_at)
                                        <span class="badge badge-success">{{ $lastCheckin->checkin_at->format('H:i:s') }}</span>
                                    @else
                                        <span style="color:#ccc">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lastCheckin && $lastCheckin->checkout_at)
                                        <span class="badge badge-warning">{{ $lastCheckin->checkout_at->format('H:i:s') }}</span>
                                    @else
                                        <span style="color:#ccc">—</span>
                                    @endif
                                </td>
                                <td><span class="badge badge-info">{{ $todayCheckins->count() }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tab 2: Timeline --}}
            <div class="tab-content" id="tabTimeline">
                @forelse($recentCheckins as $checkin)
                <div class="timeline-item">
                    <div class="timeline-time">{{ $checkin->checkin_at->format('H:i:s') }}</div>
                    <div class="timeline-content">
                        <div class="timeline-name">
                            {{ $checkin->guest->name ?? '—' }}
                            @if($checkin->station)
                                <span class="badge" style="background:#f0f2ff;color:#667eea;font-size:0.72rem;margin-left:6px;">
                                    <i class="fas fa-door-open"></i> {{ $checkin->station->name }}
                                </span>
                            @endif
                        </div>
                        <div class="timeline-action">
                            <span class="badge badge-success">Check-in</span>
                            @if($checkin->checkout_at)
                                &rarr; <span class="badge badge-warning">Check-out {{ $checkin->checkout_at->format('H:i:s') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align:center; padding:40px; color:#999;">
                    <i class="fas fa-calendar-times" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                    Chưa có lượt check-in nào trong ngày này
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');
    }

    document.getElementById('searchGuest').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.guest-stat-row').forEach(row => {
            row.style.display = row.dataset.name.includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
