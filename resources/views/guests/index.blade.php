<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Guests</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            color: #1f2328;
            background: #fafafa;
        }
        body { display: flex; }

        .guests-panel {
            width: 380px;
            height: 100vh;
            border-right: 1px solid #e5e7eb;
            background: #fff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .guests-panel > header {
            padding: 12px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .guests-panel > header h1 { margin: 0; font: inherit; color: inherit; }
        .guests-scroll {
            flex: 1;
            overflow-y: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        thead th {
            position: sticky;
            top: 0;
            background: #fff;
            text-align: left;
            font-weight: 500;
            color: #6b7280;
            padding: 10px 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        tbody td {
            padding: 10px 16px;
            border-bottom: 1px solid #f1f3f5;
        }
        tbody tr:hover { background: #f9fafb; }
        .num { text-align: right; font-variant-numeric: tabular-nums; color: #374151; }
        .guest-num {
            font: inherit;
            font-size: 14px;
            font-variant-numeric: tabular-nums;
            color: #374151;
            text-align: right;
            width: 56px;
            border: 1px solid transparent;
            background: transparent;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .guest-num:hover { border-color: #e5e7eb; }
        .guest-num:focus { outline: none; border-color: #2563eb; background: #fff; }
        .guest-num::-webkit-inner-spin-button,
        .guest-num::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .guest-num { -moz-appearance: textfield; }
        .confirm-cell { text-align: center; }
        .confirm-toggle {
            font: inherit;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 2px 10px;
            cursor: pointer;
            min-width: 36px;
        }
        .confirm-toggle.yes { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .confirm-toggle.no  { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .confirm-toggle:hover { filter: brightness(0.97); }
        .empty {
            padding: 24px 20px;
            color: #9ca3af;
            font-size: 13px;
        }

        .guests-toolbar {
            padding: 8px 20px;
            border-bottom: 1px solid #e5e7eb;
            background: #fff;
            font-size: 13px;
            color: #6b7280;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }
        .filter-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            user-select: none;
        }
        #guests-table.unassigned-only tr[data-assigned="1"] { display: none; }
        #guests-table.confirmed-yes tr:has(.confirm-toggle[data-confirmed="0"]) { display: none; }
        #guests-table.confirmed-no tr:has(.confirm-toggle[data-confirmed="1"]) { display: none; }
        #guests-table tr[data-guest-id][data-name-match="0"] { display: none; }
        .guests-toolbar .search {
            flex: 1;
            min-width: 140px;
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
        }

        .tables-panel {
            flex: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .tables-panel > header {
            padding: 12px 20px;
            border-bottom: 1px solid #e5e7eb;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .tables-panel > header h1 {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #6b7280;
            margin: 0;
        }
        .assigned-count {
            font-weight: 500;
            color: #9ca3af;
            margin-left: 4px;
            font-variant-numeric: tabular-nums;
        }
        .btn {
            font: inherit;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #1f2328;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }
        .btn:hover { background: #f3f4f6; }
        .btn-primary {
            background: #111827;
            color: #fff;
            border-color: #111827;
        }
        .btn-primary:hover { background: #1f2937; }
        .btn-danger { color: #b91c1c; }
        .btn-danger:hover { background: #fef2f2; }

        .tables-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 16px 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, 405px);
            gap: 16px;
            align-content: start;
        }
        .table-card {
            width: 405px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
        }
        .table-card header {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f3f5;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-card header .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .table-number {
            width: 64px;
            font: inherit;
            font-size: 14px;
            font-variant-numeric: tabular-nums;
            border: 1px solid transparent;
            background: transparent;
            padding: 4px 6px;
            border-radius: 4px;
        }
        .table-number:hover { border-color: #e5e7eb; }
        .table-number:focus { outline: none; border-color: #2563eb; background: #fff; }
        .table-card header .spacer { flex: 1; }
        .table-card header .guest-count {
            font-size: 12px;
            color: #6b7280;
            font-variant-numeric: tabular-nums;
        }
        .table-card.finished { background: #f9fafb; }
        .table-card.finished .table-number { pointer-events: none; color: #6b7280; }
        .table-card.finished .table-number:hover { border-color: transparent; }
        .table-card.finished .search-row,
        .table-card.finished .delete-table,
        .table-card.finished .remove-guest { display: none; }
        .table-card .finish-table { color: #166534; border-color: #bbf7d0; background: #f0fdf4; }
        .table-card .finish-table:hover { background: #dcfce7; }
        .table-card .edit-table { color: #1f2328; }
        .table-card.used { background: #fee2e2; border-color: #fecaca; }
        .table-card.used header,
        .table-card.used .search-row { border-bottom-color: #fecaca; }
        .table-card .used-table { color: #b91c1c; border-color: #fecaca; background: #fef2f2; }
        .table-card .used-table:hover { background: #fee2e2; }
        .table-card.used .used-table { background: #fecaca; }

        .search-row {
            position: relative;
            padding: 10px 14px;
            border-bottom: 1px solid #f1f3f5;
        }
        .search-row input {
            width: 100%;
            font: inherit;
            font-size: 13px;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }
        .search-row input:focus { outline: none; border-color: #2563eb; }
        .search-results {
            position: absolute;
            left: 14px;
            right: 14px;
            top: calc(100% - 4px);
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            max-height: 448px;
            overflow-y: auto;
            z-index: 5;
        }
        .search-results .item {
            padding: 8px 12px;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }
        .search-results .item:hover { background: #f3f4f6; }
        .search-results .item .meta { color: #9ca3af; font-size: 12px; }
        .search-results .item.disabled { color: #9ca3af; cursor: not-allowed; }
        .search-results .item.disabled:hover { background: #fff; }
        .search-results .none { padding: 8px 12px; font-size: 13px; color: #9ca3af; }

        .table-guests {
            list-style: none;
            margin: 0;
            padding: 4px 0;
            min-height: 40px;
        }
        .table-guests li {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            font-size: 14px;
        }
        .table-guests li:hover { background: #f9fafb; }
        .table-guests .name { flex: 1; }
        .table-guests .meta { font-size: 12px; color: #9ca3af; font-variant-numeric: tabular-nums; }
        .table-guests .empty-row {
            padding: 10px 14px;
            font-size: 13px;
            color: #9ca3af;
        }
        .icon-btn {
            background: transparent;
            border: 0;
            color: #9ca3af;
            cursor: pointer;
            font-size: 14px;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .icon-btn:hover { background: #f3f4f6; color: #b91c1c; }

        .tables-empty {
            grid-column: 1 / -1;
            color: #9ca3af;
            font-size: 14px;
            text-align: center;
            padding: 40px 20px;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(17, 24, 39, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }
        .modal-backdrop[hidden] { display: none; }
        .modal {
            background: #fff;
            border-radius: 12px;
            width: min(420px, calc(100% - 32px));
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .modal header {
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }
        .modal form { padding: 16px 20px; display: flex; flex-direction: column; gap: 12px; }
        .modal label {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 600;
        }
        .modal input[type="text"],
        .modal input[type="number"] {
            font: inherit;
            font-size: 14px;
            color: #1f2328;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 400;
            padding: 8px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }
        .modal input:focus { outline: none; border-color: #2563eb; }
        .modal .checkbox {
            flex-direction: row;
            align-items: center;
            gap: 8px;
            text-transform: none;
            letter-spacing: normal;
            color: #1f2328;
            font-weight: 400;
            font-size: 14px;
        }
        .modal .checkbox input { width: 16px; height: 16px; }
        .modal .row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .modal footer {
            padding: 12px 20px 16px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            border-top: 1px solid #f1f3f5;
        }
    </style>
</head>
<body>
    <aside class="guests-panel">
        <header>
            <h1>Guests</h1>
            <div style="display:flex; gap:8px;">
                <a href="/admin" class="btn" style="text-decoration:none;">← Admin</a>
                <button id="add-guest" class="btn btn-primary" type="button">Add guest</button>
            </div>
        </header>
        <div class="guests-toolbar">
            <input id="filter-name" class="search" type="search" placeholder="Search by name…" autocomplete="off">
            <label class="filter-toggle">
                <input id="filter-unassigned" type="checkbox">
                Only unassigned
            </label>
            <label class="filter-toggle">
                Confirmed
                <select id="filter-confirmed">
                    <option value="">All</option>
                    <option value="1">Da</option>
                    <option value="0">Nu</option>
                </select>
            </label>
        </div>
        <div class="guests-scroll">
            <table id="guests-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="num">Persons</th>
                            <th class="num">Kids</th>
                            <th class="confirm-cell">Confirmed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($guests->isEmpty())
                            <tr class="empty-guests"><td colspan="4" class="empty">No guests yet.</td></tr>
                        @endif
                        @foreach ($guests as $guest)
                            <tr data-guest-id="{{ $guest->id }}" data-assigned="{{ $guest->wedding_table_id ? '1' : '0' }}">
                                <td>{{ $guest->name }}</td>
                                <td class="num">
                                    <input
                                        type="number"
                                        min="0"
                                        class="guest-num"
                                        data-field="person_number"
                                        value="{{ $guest->person_number }}"
                                    >
                                </td>
                                <td class="num">
                                    <input
                                        type="number"
                                        min="0"
                                        class="guest-num"
                                        data-field="kid_number"
                                        value="{{ $guest->kid_number }}"
                                    >
                                </td>
                                <td class="confirm-cell">
                                    <button
                                        type="button"
                                        class="confirm-toggle {{ $guest->confirmed ? 'yes' : 'no' }}"
                                        data-confirmed="{{ $guest->confirmed ? '1' : '0' }}"
                                        title="Click to toggle"
                                    >{{ $guest->confirmed ? 'Da' : 'Nu' }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
    </aside>

    <div id="add-guest-modal" class="modal-backdrop" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="add-guest-title">
            <header><span id="add-guest-title">Add guest</span></header>
            <form id="add-guest-form">
                <label>
                    Name
                    <input name="name" type="text" maxlength="255" required autocomplete="off">
                </label>
                <div class="row-2">
                    <label>
                        Persons
                        <input name="person_number" type="number" min="0" value="0" required>
                    </label>
                    <label>
                        Kids
                        <input name="kid_number" type="number" min="0" value="0" required>
                    </label>
                </div>
                <label class="checkbox">
                    <input name="confirmed" type="checkbox">
                    Confirmed
                </label>
                <label class="checkbox">
                    <input name="accommodation" type="checkbox">
                    Accomodation
                </label>
                <footer>
                    <button type="button" class="btn" data-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </footer>
            </form>
        </div>
    </div>

    @php
        $assignedPersons = $tables->sum(fn ($t) => $t->guests->sum('person_number'));
        $assignedKids = $tables->sum(fn ($t) => $t->guests->sum('kid_number'));
    @endphp
    <main class="tables-panel">
        <header>
            <h1>
                Tables <span id="tables-count" class="tables-count">({{ $tables->count() }})</span>
                <span id="assigned-count" class="assigned-count">· {{ $assignedPersons }} persons · {{ $assignedKids }} kids</span>
            </h1>
            <button id="add-table" class="btn btn-primary" type="button">Add table</button>
        </header>
        <div id="tables-scroll" class="tables-scroll">
            @forelse ($tables as $table)
                @php
                    $persons = $table->guests->sum('person_number');
                    $kids = $table->guests->sum('kid_number');
                @endphp
                <article class="table-card{{ $table->finished ? ' finished' : '' }}{{ $table->used ? ' used' : '' }}" data-table-id="{{ $table->id }}" data-finished="{{ $table->finished ? '1' : '0' }}" data-used="{{ $table->used ? '1' : '0' }}">
                    <header>
                        <span class="label">Table</span>
                        <input class="table-number" type="number" min="1" value="{{ $table->number }}"{{ $table->finished ? ' disabled' : '' }}>
                        <span class="guest-count">{{ $persons }} persons · {{ $kids }} kids</span>
                        <span class="spacer"></span>
                        @if ($table->finished)
                            <button class="btn edit-table" type="button">Edit</button>
                            <button class="btn used-table" type="button">{{ $table->used ? 'Used ✓' : 'Used' }}</button>
                        @else
                            <button class="btn finish-table" type="button">Finish</button>
                            <button class="btn btn-danger delete-table" type="button">Delete</button>
                        @endif
                    </header>
                    <div class="search-row">
                        <input type="search" class="guest-search" placeholder="Search a guest to add…" autocomplete="off">
                        <div class="search-results" hidden></div>
                    </div>
                    <ul class="table-guests">
                        @forelse ($table->guests as $guest)
                            <li data-guest-id="{{ $guest->id }}" data-person-number="{{ $guest->person_number }}" data-kid-number="{{ $guest->kid_number }}">
                                <span class="name">{{ $guest->name }}</span>
                                <span class="meta">{{ $guest->person_number }}p · {{ $guest->kid_number }}k</span>
                                <button class="icon-btn remove-guest" type="button" title="Remove">×</button>
                            </li>
                        @empty
                            <li class="empty-row">No guests at this table yet.</li>
                        @endforelse
                    </ul>
                </article>
            @empty
                <div class="tables-empty">No tables yet — click <strong>Add table</strong> to create one.</div>
            @endforelse
        </div>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const API_BASE = @json(url('/mese/config'));

        async function api(url, options = {}) {
            const res = await fetch(API_BASE + url, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {}),
                },
                ...options,
            });
            if (!res.ok) {
                const text = await res.text();
                throw new Error(`${res.status} ${res.statusText}: ${text}`);
            }
            return res.status === 204 ? null : res.json();
        }

        const scroll = document.getElementById('tables-scroll');

        function buildCard(table) {
            const card = document.createElement('article');
            card.className = 'table-card';
            card.dataset.tableId = table.id;
            card.dataset.finished = table.finished ? '1' : '0';
            card.dataset.used = table.used ? '1' : '0';
            if (table.finished) card.classList.add('finished');
            if (table.used) card.classList.add('used');
            const actions = table.finished
                ? `<button class="btn edit-table" type="button">Edit</button>`
                : `<button class="btn finish-table" type="button">Finish</button>
                   <button class="btn btn-danger delete-table" type="button">Delete</button>`;
            card.innerHTML = `
                <header>
                    <span class="label">Table</span>
                    <input class="table-number" type="number" min="1" value="${table.number}"${table.finished ? ' disabled' : ''}>
                    <span class="guest-count">0 persons · 0 kids</span>
                    <span class="spacer"></span>
                    ${actions}
                </header>
                <div class="search-row">
                    <input type="search" class="guest-search" placeholder="Search a guest to add…" autocomplete="off">
                    <div class="search-results" hidden></div>
                </div>
                <ul class="table-guests">
                    <li class="empty-row">No guests at this table yet.</li>
                </ul>
            `;
            return card;
        }

        function applyFinishedState(card, finished, used) {
            card.dataset.finished = finished ? '1' : '0';
            card.classList.toggle('finished', !!finished);
            const numberInput = card.querySelector('.table-number');
            if (numberInput) numberInput.disabled = !!finished;
            const header = card.querySelector('header');
            const existingFinish = header.querySelector('.finish-table');
            const existingEdit = header.querySelector('.edit-table');
            const existingDelete = header.querySelector('.delete-table');
            const existingUsed = header.querySelector('.used-table');
            if (finished) {
                existingFinish?.remove();
                existingDelete?.remove();
                if (!existingEdit) {
                    const btn = document.createElement('button');
                    btn.className = 'btn edit-table';
                    btn.type = 'button';
                    btn.textContent = 'Edit';
                    header.appendChild(btn);
                }
                if (!existingUsed) {
                    const btn = document.createElement('button');
                    btn.className = 'btn used-table';
                    btn.type = 'button';
                    btn.textContent = used ? 'Used ✓' : 'Used';
                    header.appendChild(btn);
                } else {
                    existingUsed.textContent = used ? 'Used ✓' : 'Used';
                }
                card.dataset.used = used ? '1' : '0';
                card.classList.toggle('used', !!used);
            } else {
                existingEdit?.remove();
                existingUsed?.remove();
                card.dataset.used = '0';
                card.classList.remove('used');
                if (!existingFinish) {
                    const btn = document.createElement('button');
                    btn.className = 'btn finish-table';
                    btn.type = 'button';
                    btn.textContent = 'Finish';
                    header.appendChild(btn);
                }
                if (!existingDelete) {
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-danger delete-table';
                    btn.type = 'button';
                    btn.textContent = 'Delete';
                    header.appendChild(btn);
                }
            }
            const search = card.querySelector('.search-row .guest-search');
            if (search) search.value = '';
            const results = card.querySelector('.search-results');
            if (results) { results.hidden = true; results.innerHTML = ''; }
        }

        function ensureEmptyRow(list) {
            if (list.querySelector('li[data-guest-id]')) {
                const empty = list.querySelector('.empty-row');
                if (empty) empty.remove();
            } else if (!list.querySelector('.empty-row')) {
                const li = document.createElement('li');
                li.className = 'empty-row';
                li.textContent = 'No guests at this table yet.';
                list.appendChild(li);
            }
        }

        function updateTablesCount() {
            const el = document.getElementById('tables-count');
            if (!el) return;
            const n = scroll.querySelectorAll('.table-card').length;
            el.textContent = `(${n})`;
        }

        function updateGuestCount(card) {
            const label = card.querySelector('.guest-count');
            if (!label) return;
            let persons = 0, kids = 0;
            card.querySelectorAll('.table-guests li[data-guest-id]').forEach(li => {
                persons += parseInt(li.dataset.personNumber, 10) || 0;
                kids += parseInt(li.dataset.kidNumber, 10) || 0;
            });
            label.textContent = `${persons} persons · ${kids} kids`;
            updateAssignedTotals();
        }

        function updateAssignedTotals() {
            const el = document.getElementById('assigned-count');
            if (!el) return;
            let persons = 0, kids = 0;
            scroll.querySelectorAll('.table-card .table-guests li[data-guest-id]').forEach(li => {
                persons += parseInt(li.dataset.personNumber, 10) || 0;
                kids += parseInt(li.dataset.kidNumber, 10) || 0;
            });
            el.textContent = `· ${persons} persons · ${kids} kids`;
        }

        function appendGuest(card, guest) {
            const list = card.querySelector('.table-guests');
            const li = document.createElement('li');
            li.dataset.guestId = guest.id;
            li.dataset.personNumber = String(guest.person_number ?? 0);
            li.dataset.kidNumber = String(guest.kid_number ?? 0);
            li.innerHTML = `
                <span class="name"></span>
                <span class="meta"></span>
                <button class="icon-btn remove-guest" type="button" title="Remove">×</button>
            `;
            li.querySelector('.name').textContent = guest.name;
            li.querySelector('.meta').textContent = `${guest.person_number}p · ${guest.kid_number}k`;
            list.appendChild(li);
            ensureEmptyRow(list);
            updateGuestCount(card);
        }

        document.getElementById('add-table').addEventListener('click', async () => {
            try {
                const table = await api('/tables', { method: 'POST' });
                const empty = scroll.querySelector('.tables-empty');
                if (empty) empty.remove();
                scroll.appendChild(buildCard(table));
                updateTablesCount();
            } catch (err) {
                alert('Failed to add table: ' + err.message);
            }
        });

        scroll.addEventListener('click', async (e) => {
            const card = e.target.closest('.table-card');
            if (!card) return;
            const tableId = card.dataset.tableId;

            if (e.target.classList.contains('finish-table')) {
                try {
                    const updated = await api(`/tables/${tableId}`, {
                        method: 'PATCH',
                        body: JSON.stringify({ finished: true }),
                    });
                    applyFinishedState(card, !!updated.finished, !!updated.used);
                } catch (err) {
                    alert('Failed to finish: ' + err.message);
                }
                return;
            }

            if (e.target.classList.contains('edit-table')) {
                try {
                    const updated = await api(`/tables/${tableId}`, {
                        method: 'PATCH',
                        body: JSON.stringify({ finished: false }),
                    });
                    applyFinishedState(card, !!updated.finished, !!updated.used);
                } catch (err) {
                    alert('Failed to edit: ' + err.message);
                }
                return;
            }

            if (e.target.classList.contains('used-table')) {
                const next = card.dataset.used !== '1';
                try {
                    const updated = await api(`/tables/${tableId}`, {
                        method: 'PATCH',
                        body: JSON.stringify({ used: next }),
                    });
                    card.dataset.used = updated.used ? '1' : '0';
                    card.classList.toggle('used', !!updated.used);
                    e.target.textContent = updated.used ? 'Used ✓' : 'Used';
                } catch (err) {
                    alert('Failed to update: ' + err.message);
                }
                return;
            }

            if (e.target.classList.contains('delete-table')) {
                if (card.dataset.finished === '1') return;
                if (!confirm('Delete this table? Guests will become unassigned.')) return;
                try {
                    await api(`/tables/${tableId}`, { method: 'DELETE' });
                    card.querySelectorAll('li[data-guest-id]').forEach(li => {
                        const guestRow = guestsBody?.querySelector(`tr[data-guest-id="${li.dataset.guestId}"]`);
                        if (guestRow) guestRow.dataset.assigned = '0';
                    });
                    card.remove();
                    if (!scroll.querySelector('.table-card')) {
                        const div = document.createElement('div');
                        div.className = 'tables-empty';
                        div.innerHTML = 'No tables yet — click <strong>Add table</strong> to create one.';
                        scroll.appendChild(div);
                    }
                    updateTablesCount();
                    updateAssignedTotals();
                } catch (err) {
                    alert('Failed to delete: ' + err.message);
                }
                return;
            }

            if (e.target.classList.contains('remove-guest')) {
                const li = e.target.closest('li[data-guest-id]');
                const guestId = li.dataset.guestId;
                const name = li.querySelector('.name')?.textContent.trim() ?? 'this guest';
                if (!confirm(`Remove ${name} from this table?`)) return;
                try {
                    await api(`/tables/${tableId}/guests/${guestId}`, { method: 'DELETE' });
                    li.remove();
                    ensureEmptyRow(card.querySelector('.table-guests'));
                    updateGuestCount(card);
                    const guestRow = guestsBody?.querySelector(`tr[data-guest-id="${guestId}"]`);
                    if (guestRow) guestRow.dataset.assigned = '0';
                } catch (err) {
                    alert('Failed to remove guest: ' + err.message);
                }
            }
        });

        scroll.addEventListener('change', async (e) => {
            if (!e.target.classList.contains('table-number')) return;
            const card = e.target.closest('.table-card');
            const tableId = card.dataset.tableId;
            const newNumber = parseInt(e.target.value, 10);
            if (!Number.isFinite(newNumber) || newNumber < 1) {
                alert('Number must be a positive integer.');
                return;
            }
            try {
                const updated = await api(`/tables/${tableId}`, {
                    method: 'PATCH',
                    body: JSON.stringify({ number: newNumber }),
                });
                e.target.value = updated.number;
            } catch (err) {
                alert('Failed to update number: ' + err.message);
            }
        });

        const searchTimers = new WeakMap();
        scroll.addEventListener('input', (e) => {
            if (!e.target.classList.contains('guest-search')) return;
            const input = e.target;
            const card = input.closest('.table-card');
            const results = card.querySelector('.search-results');

            clearTimeout(searchTimers.get(input));
            const t = setTimeout(() => runSearch(card, input, results), 150);
            searchTimers.set(input, t);
        });

        async function runSearch(card, input, results) {
            const q = input.value.trim();
            if (!q) {
                results.hidden = true;
                results.innerHTML = '';
                return;
            }
            try {
                const guests = await api(`/guests/search?q=${encodeURIComponent(q)}`);
                renderResults(card, results, guests);
            } catch (err) {
                results.innerHTML = `<div class="none">Error: ${err.message}</div>`;
                results.hidden = false;
            }
        }

        function renderResults(card, results, guests) {
            const tableId = card.dataset.tableId;
            results.innerHTML = '';
            if (guests.length === 0) {
                results.innerHTML = '<div class="none">No matches.</div>';
                results.hidden = false;
                return;
            }
            for (const g of guests) {
                const item = document.createElement('div');
                item.className = 'item';
                const onSameTable = String(g.wedding_table_id) === String(tableId);
                const assignedElsewhere = g.wedding_table_id && !onSameTable;
                if (onSameTable) item.classList.add('disabled');
                item.innerHTML = `
                    <span class="name"></span>
                    <span class="meta"></span>
                `;
                item.querySelector('.name').textContent = g.name;
                const metaBits = [`${g.person_number}p · ${g.kid_number}k`];
                if (onSameTable) metaBits.push('already here');
                else if (assignedElsewhere) metaBits.push('move from another table');
                item.querySelector('.meta').textContent = metaBits.join(' · ');
                if (!onSameTable) {
                    item.addEventListener('click', () => assignGuest(card, g, results));
                }
                results.appendChild(item);
            }
            results.hidden = false;
        }

        async function assignGuest(card, guest, results) {
            const tableId = card.dataset.tableId;
            try {
                const updated = await api(`/tables/${tableId}/guests`, {
                    method: 'POST',
                    body: JSON.stringify({ guest_id: guest.id }),
                });

                document.querySelectorAll('.table-card').forEach(other => {
                    if (other === card) return;
                    const li = other.querySelector(`li[data-guest-id="${guest.id}"]`);
                    if (li) {
                        li.remove();
                        ensureEmptyRow(other.querySelector('.table-guests'));
                        updateGuestCount(other);
                    }
                });

                if (!card.querySelector(`li[data-guest-id="${guest.id}"]`)) {
                    appendGuest(card, updated);
                }

                const guestRow = guestsBody?.querySelector(`tr[data-guest-id="${guest.id}"]`);
                if (guestRow) guestRow.dataset.assigned = '1';

                const input = card.querySelector('.guest-search');
                input.value = '';
                results.hidden = true;
                results.innerHTML = '';
            } catch (err) {
                alert('Failed to assign: ' + err.message);
            }
        }

        document.addEventListener('click', (e) => {
            if (e.target.closest('.search-row')) return;
            document.querySelectorAll('.search-results').forEach(r => {
                r.hidden = true;
            });
        });

        const guestsTable = document.getElementById('guests-table');
        const guestsBody = guestsTable?.querySelector('tbody');
        const filterUnassigned = document.getElementById('filter-unassigned');
        filterUnassigned?.addEventListener('change', () => {
            guestsTable?.classList.toggle('unassigned-only', filterUnassigned.checked);
        });

        const filterConfirmed = document.getElementById('filter-confirmed');
        filterConfirmed?.addEventListener('change', () => {
            guestsTable?.classList.toggle('confirmed-yes', filterConfirmed.value === '1');
            guestsTable?.classList.toggle('confirmed-no', filterConfirmed.value === '0');
        });

        const filterName = document.getElementById('filter-name');
        function applyNameFilter(tr) {
            const q = (filterName?.value ?? '').trim().toLowerCase();
            const name = tr.querySelector('td')?.textContent?.toLowerCase() ?? '';
            tr.dataset.nameMatch = !q || name.includes(q) ? '1' : '0';
        }
        filterName?.addEventListener('input', () => {
            guestsBody?.querySelectorAll('tr[data-guest-id]').forEach(applyNameFilter);
        });

        function buildGuestRow(g) {
            const tr = document.createElement('tr');
            tr.dataset.guestId = g.id;
            tr.dataset.assigned = g.wedding_table_id ? '1' : '0';
            tr.innerHTML = `
                <td></td>
                <td class="num"><input type="number" min="0" class="guest-num" data-field="person_number"></td>
                <td class="num"><input type="number" min="0" class="guest-num" data-field="kid_number"></td>
                <td class="confirm-cell">
                    <button type="button" class="confirm-toggle" title="Click to toggle"></button>
                </td>
            `;
            tr.querySelector('td').textContent = g.name;
            const inputs = tr.querySelectorAll('.guest-num');
            inputs[0].value = g.person_number;
            inputs[0].defaultValue = String(g.person_number);
            inputs[1].value = g.kid_number;
            inputs[1].defaultValue = String(g.kid_number);
            const btn = tr.querySelector('.confirm-toggle');
            btn.dataset.confirmed = g.confirmed ? '1' : '0';
            btn.textContent = g.confirmed ? 'Da' : 'Nu';
            btn.classList.add(g.confirmed ? 'yes' : 'no');
            return tr;
        }

        function insertGuestRow(g) {
            if (!guestsBody) return;
            guestsBody.querySelector('.empty-guests')?.remove();
            const row = buildGuestRow(g);
            const rows = Array.from(guestsBody.querySelectorAll('tr[data-guest-id]'));
            const next = rows.find(r => {
                const cell = r.querySelector('td');
                return cell && cell.textContent.localeCompare(g.name, undefined, { sensitivity: 'base' }) > 0;
            });
            if (next) guestsBody.insertBefore(row, next);
            else guestsBody.appendChild(row);
            applyNameFilter(row);
        }

        const addGuestBtn = document.getElementById('add-guest');
        const addGuestModal = document.getElementById('add-guest-modal');
        const addGuestForm = document.getElementById('add-guest-form');

        function openAddGuest() {
            addGuestForm.reset();
            addGuestModal.hidden = false;
            addGuestForm.querySelector('input[name="name"]').focus();
        }
        function closeAddGuest() { addGuestModal.hidden = true; }

        addGuestBtn?.addEventListener('click', openAddGuest);
        addGuestModal?.addEventListener('click', (e) => {
            if (e.target === addGuestModal || e.target.matches('[data-close]')) closeAddGuest();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !addGuestModal.hidden) closeAddGuest();
        });

        addGuestForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(addGuestForm);
            const payload = {
                name: String(fd.get('name') ?? '').trim(),
                person_number: parseInt(fd.get('person_number') ?? '0', 10) || 0,
                kid_number: parseInt(fd.get('kid_number') ?? '0', 10) || 0,
                confirmed: addGuestForm.elements.confirmed.checked,
                accommodation: addGuestForm.elements.accommodation.checked,
            };
            if (!payload.name) {
                alert('Name is required.');
                return;
            }
            const submit = addGuestForm.querySelector('button[type="submit"]');
            submit.disabled = true;
            try {
                const guest = await api('/guests', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });
                insertGuestRow(guest);
                closeAddGuest();
            } catch (err) {
                alert('Failed to add guest: ' + err.message);
            } finally {
                submit.disabled = false;
            }
        });

        if (guestsTable) {
            guestsTable.addEventListener('click', async (e) => {
                const btn = e.target.closest('.confirm-toggle');
                if (!btn) return;
                const row = btn.closest('tr[data-guest-id]');
                const guestId = row.dataset.guestId;
                const next = btn.dataset.confirmed !== '1';
                const name = row.querySelector('td')?.textContent.trim() ?? 'this guest';
                const label = next ? 'confirmed (Da)' : 'not confirmed (Nu)';
                if (!confirm(`Mark ${name} as ${label}?`)) return;
                btn.disabled = true;
                try {
                    const updated = await api(`/guests/${guestId}`, {
                        method: 'PATCH',
                        body: JSON.stringify({ confirmed: next }),
                    });
                    btn.dataset.confirmed = updated.confirmed ? '1' : '0';
                    btn.textContent = updated.confirmed ? 'Da' : 'Nu';
                    btn.classList.toggle('yes', !!updated.confirmed);
                    btn.classList.toggle('no', !updated.confirmed);
                } catch (err) {
                    alert('Failed to update: ' + err.message);
                } finally {
                    btn.disabled = false;
                }
            });

            const fieldLabels = { person_number: 'persons', kid_number: 'kids' };
            guestsTable.addEventListener('change', async (e) => {
                const input = e.target.closest('.guest-num');
                if (!input) return;
                const row = input.closest('tr[data-guest-id]');
                const guestId = row.dataset.guestId;
                const field = input.dataset.field;
                const value = parseInt(input.value, 10);
                const previous = input.defaultValue;
                if (!Number.isFinite(value) || value < 0) {
                    alert('Value must be a non-negative integer.');
                    input.value = previous;
                    return;
                }
                if (String(value) === previous) return;

                const name = row.querySelector('td')?.textContent.trim() ?? 'this guest';
                const label = fieldLabels[field] ?? field;
                if (!confirm(`Change ${label} for ${name} from ${previous} to ${value}?`)) {
                    input.value = previous;
                    return;
                }

                input.disabled = true;
                try {
                    const updated = await api(`/guests/${guestId}`, {
                        method: 'PATCH',
                        body: JSON.stringify({ [field]: value }),
                    });
                    input.value = updated[field];
                    input.defaultValue = String(updated[field]);
                } catch (err) {
                    alert('Failed to update: ' + err.message);
                    input.value = previous;
                } finally {
                    input.disabled = false;
                }
            });
        }
    </script>
</body>
</html>
