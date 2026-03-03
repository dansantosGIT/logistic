<script>
(function(){
    if(window.formatLocalISO) return;
    // formatLocalISO(iso, fallback) -> "Month D, YYYY at h:mm AM/PM"
    window.formatLocalISO = function(iso, fallback){
        if(!iso) return fallback || '';
        try{
            let s = String(iso).trim();
            // tolerate timezone minute shorthand like +00:0 -> +00:00
            if(/[+-]\d{2}:\d$/.test(s)) s = s + '0';
            // if timezone is just +HH (no minutes) -> add :00
            if(/[+-]\d{2}$/.test(s)) s = s + ':00';
            // accept space-separated datetime and convert to ISO-like
            if(s.indexOf('T') === -1 && s.indexOf(' ') !== -1) s = s.replace(' ', 'T');
            const d = new Date(s);
            if(isNaN(d)) return fallback || String(iso);
            const dateStr = new Intl.DateTimeFormat(undefined, { year:'numeric', month:'long', day:'numeric' }).format(d);
            const timeStr = new Intl.DateTimeFormat(undefined, { hour:'numeric', minute:'2-digit', hour12:true }).format(d);
            return `${dateStr} at ${timeStr}`;
        }catch(e){ return fallback || String(iso); }
    };
})();
</script>
