@php $bgUrl = asset('images/welcome-bg.jpg'); $logoUrl = asset('images/favi.png'); @endphp
<link rel="preload" as="image" href="{{ $bgUrl }}">
<link rel="preload" as="image" href="{{ $logoUrl }}">
<style>
    /* Placeholder and class-based swap for the full-bleed background */
    .bg{position:fixed;inset:0;background-image:linear-gradient(180deg,#e9f0fb,#f6fbf9);background-size:cover;background-position:center center;filter:brightness(0.6) saturate(0.95);z-index:-3;transition:opacity .28s ease}
    .bg.has-image{background-image:url('{{ $bgUrl }}');background-size:cover;background-position:center center}
</style>
<script>
    (function(){
        function applyBgAndLogo(){
            try{
                // Background
                var bg = document.querySelector('.bg');
                if(bg){
                    var img = new Image();
                    img.src = '{{ $bgUrl }}';
                    if(img.complete) bg.classList.add('has-image');
                    else img.onload = function(){ bg.classList.add('has-image'); };
                }

                // Topbar / brand logo: prefer showing immediately
                var logoSelectors = ['img.logo-img', '.topbar img', '.brand img'];
                var logos = [];
                logoSelectors.forEach(function(s){ document.querySelectorAll(s).forEach(function(i){ logos.push(i); }); });
                logos.forEach(function(i){
                    try{
                        // set attributes that help immediate decode/display
                        i.loading = 'eager';
                        i.decoding = 'async';
                        // ensure src is set to canonical logo (covers cases using data-src)
                        if(!i.getAttribute('src') || i.getAttribute('src').indexOf('data:')===0){
                            i.src = '{{ $logoUrl }}';
                        }
                        // set width/height to avoid layout shift if not present
                        if(!i.getAttribute('width')) i.setAttribute('width', 40);
                        if(!i.getAttribute('height')) i.setAttribute('height', 40);
                    }catch(e){}
                });
            }catch(e){console && console.error && console.error('bg-preload', e)}
        }

        if(document.readyState === 'loading'){
            document.addEventListener('DOMContentLoaded', applyBgAndLogo);
        } else {
            applyBgAndLogo();
        }
    })();
</script>
