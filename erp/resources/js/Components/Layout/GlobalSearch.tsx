import { useState, useEffect, useRef, useCallback } from 'react';

interface SearchResult {
    id: number;
    title: string;
    subtitle: string;
    url: string;
    type: string;
}

const TYPE_COLORS: Record<string, string> = {
    Product:  'bg-blue-100 text-blue-700',
    Invoice:  'bg-green-100 text-green-700',
    Contact:  'bg-purple-100 text-purple-700',
    Lead:     'bg-orange-100 text-orange-700',
    Ticket:   'bg-red-100 text-red-700',
    Employee: 'bg-indigo-100 text-indigo-700',
    Project:  'bg-teal-100 text-teal-700',
    Order:    'bg-pink-100 text-pink-700',
};

export default function GlobalSearch() {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<SearchResult[]>([]);
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [activeIndex, setActiveIndex] = useState(-1);
    const inputRef = useRef<HTMLInputElement>(null);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    // Cmd+K shortcut
    useEffect(() => {
        const handler = (e: KeyboardEvent) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                inputRef.current?.focus();
                setOpen(true);
            }
            if (e.key === 'Escape') setOpen(false);
        };
        window.addEventListener('keydown', handler);
        return () => window.removeEventListener('keydown', handler);
    }, []);

    const search = useCallback((q: string) => {
        if (q.length < 2) { setResults([]); setOpen(false); return; }
        setLoading(true);
        fetch(`/search?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => { setResults(data.results ?? []); setOpen(true); setLoading(false); setActiveIndex(-1); })
        .catch(() => setLoading(false));
    }, []);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const val = e.target.value;
        setQuery(val);
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => search(val), 300);
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (!open) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); setActiveIndex(i => Math.min(i + 1, results.length - 1)); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); setActiveIndex(i => Math.max(i - 1, 0)); }
        if (e.key === 'Enter' && activeIndex >= 0) { window.location.href = results[activeIndex].url; }
        if (e.key === 'Escape') { setOpen(false); }
    };

    return (
        <div className="relative w-72">
            <div className="relative">
                <svg className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input
                    ref={inputRef}
                    type="text"
                    value={query}
                    onChange={handleChange}
                    onKeyDown={handleKeyDown}
                    onFocus={() => query.length >= 2 && setOpen(true)}
                    placeholder="Search... (⌘K)"
                    className="w-full pl-9 pr-3 py-1.5 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                />
                {loading && <div className="absolute right-3 top-1/2 -translate-y-1/2 h-3 w-3 border border-slate-400 border-t-transparent rounded-full animate-spin" />}
            </div>
            {open && results.length > 0 && (
                <div className="absolute top-full mt-1 left-0 right-0 bg-white border border-slate-200 rounded-lg shadow-lg z-50 max-h-80 overflow-y-auto">
                    {results.map((r, i) => (
                        <a key={`${r.type}-${r.id}`} href={r.url}
                           className={`flex items-start gap-3 px-3 py-2 hover:bg-slate-50 cursor-pointer ${i === activeIndex ? 'bg-slate-50' : ''}`}
                           onClick={() => setOpen(false)}>
                            <span className={`mt-0.5 shrink-0 inline-flex items-center rounded-full px-1.5 py-0.5 text-xs font-medium ${TYPE_COLORS[r.type] ?? 'bg-slate-100 text-slate-600'}`}>{r.type}</span>
                            <div className="min-w-0">
                                <p className="text-sm font-medium text-slate-900 truncate">{r.title}</p>
                                {r.subtitle && <p className="text-xs text-slate-500 truncate">{r.subtitle}</p>}
                            </div>
                        </a>
                    ))}
                </div>
            )}
            {open && query.length >= 2 && results.length === 0 && !loading && (
                <div className="absolute top-full mt-1 left-0 right-0 bg-white border border-slate-200 rounded-lg shadow-lg z-50 px-3 py-4 text-center text-sm text-slate-500">
                    No results for "{query}"
                </div>
            )}
        </div>
    );
}
