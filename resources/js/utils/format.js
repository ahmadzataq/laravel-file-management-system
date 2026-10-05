const dateFormatter = new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

/** "2026-10-03T10:15:00+00:00" -> "03 Oct 2026, 10:15" (shown in the browser's time zone) */
export function formatDate(iso) {
    return iso ? dateFormatter.format(new Date(iso)) : '—';
}

/** 1536 -> "1.5 KB" */
export function formatSize(bytes) {
    if (bytes === null || bytes === undefined) {
        return '—';
    }

    const units = ['B', 'KB', 'MB', 'GB'];
    let value = bytes;
    let unit = 0;

    while (value >= 1024 && unit < units.length - 1) {
        value /= 1024;
        unit++;
    }

    return `${unit === 0 ? value : value.toFixed(1)} ${units[unit]}`;
}

/** "report.final.pdf" -> "PDF" */
export function fileExtension(name) {
    const parts = (name ?? '').split('.');

    return parts.length > 1 ? parts.pop().toUpperCase() : 'FILE';
}

/** plural(1, 'file') -> "1 file", plural(3, 'file') -> "3 files" */
export function plural(count, word) {
    return `${count} ${word}${count === 1 ? '' : 's'}`;
}
