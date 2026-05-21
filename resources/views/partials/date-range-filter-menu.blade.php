@props(['optionClass' => 'admin-agent-chart-filter-option'])

<button type="button"
    class="{{ $optionClass }} block w-full px-4 py-2.5 text-left text-sm text-concierge-navy transition hover:bg-slate-50"
    role="menuitem" data-filter="today" data-filter-label="Today">Today</button>
<button type="button"
    class="{{ $optionClass }} block w-full px-4 py-2.5 text-left text-sm text-concierge-navy transition hover:bg-slate-50"
    role="menuitem" data-filter="week" data-filter-label="This week">This week</button>
<button type="button"
    class="{{ $optionClass }} block w-full px-4 py-2.5 text-left text-sm text-concierge-navy transition hover:bg-slate-50"
    role="menuitem" data-filter="month" data-filter-label="This month">This month</button>
<button type="button"
    class="{{ $optionClass }} block w-full px-4 py-2.5 text-left text-sm text-concierge-navy transition hover:bg-slate-50"
    role="menuitem" data-filter="year" data-filter-label="This year">This year</button>
<button type="button"
    class="{{ $optionClass }} block w-full px-4 py-2.5 text-left text-sm text-concierge-navy transition hover:bg-slate-50"
    role="menuitem" data-filter="custom" data-filter-label="Custom date">Custom date</button>
