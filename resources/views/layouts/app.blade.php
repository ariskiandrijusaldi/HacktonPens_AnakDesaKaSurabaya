<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="CampusCare — Ruang aman dan anonim untuk melaporkan kekerasan seksual di lingkungan kampus.">

    <title>@yield('title', 'CampusCare — Layanan Satgas PPKS Kampus')</title>

    @vite(['resources/css/campuscare.css'])
    @stack('head')

    {{-- ===== TAMPILAN BARU ala Toyota (CSS inline, tidak bergantung pada Vite) ===== --}}
    <style>
  .cc{--ink:#111;--mute:#444;--line:#ddd;--brand:#eb0a1e;font-family:"Segoe UI",Inter,Arial,sans-serif;color:var(--ink)}
  .cc *{box-sizing:border-box}
  .cc a{color:inherit;text-decoration:none}
  .cc svg{width:24px;height:24px;flex:none}
  .cc-nav{position:sticky;top:0;z-index:50;height:80px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;padding:0 clamp(16px,7vw,140px)}
  .cc-logo{display:flex;align-items:center;gap:10px;font-weight:700;font-size:24px;letter-spacing:.02em}
  .cc-logo i{display:grid;place-items:center;width:40px;height:40px;background:var(--brand);color:#fff;font-style:normal}
  .cc-logo i svg{width:22px;height:22px}
  .cc-menu{display:flex;gap:48px;margin-left:80px;font-size:19px;font-weight:500;color:var(--mute)}
  .cc-menu a{padding:6px 0;border-bottom:2px solid transparent;transition:.25s}
  .cc-menu a:hover,.cc-menu a:focus-visible{color:var(--ink);border-color:var(--ink)}
  .cc-acc{margin-left:auto;display:flex;align-items:center;gap:10px;font-size:19px;font-weight:500;color:var(--mute)}
  .cc-acc:hover{color:var(--ink)}
  .cc-burger{display:none;margin-left:auto;background:none;border:0;cursor:pointer;color:var(--ink)}
  .cc-hero{position:relative;height:calc(100vh - 80px);min-height:560px;max-height:800px;overflow:hidden;background:#1d2b36}
  .cc-slide{position:absolute;inset:0;opacity:0;transition:opacity .6s ease;background-size:cover;background-position:center;background-repeat:no-repeat}
  .cc-slide.on{opacity:1}
  .cc-slide::after{content:"";position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.8) 0%,rgba(0,0,0,.55) 50%,rgba(0,0,0,.4) 100%)}
  .cc-txt{position:absolute;z-index:5;left:0;right:0;top:50%;transform:translateY(-50%);text-align:center;color:#fff;padding:0 clamp(24px,10vw,200px);text-shadow:0 2px 14px rgba(0,0,0,.45)}
  .cc-txt h1{margin:0;font-size:clamp(36px,5vw,64px);font-weight:700;line-height:1.05}
  .cc-txt p{margin:10px 0 0;font-size:clamp(18px,2.2vw,30px)}
  .cc-txt strong{display:block;margin-top:18px;font-size:clamp(26px,3vw,40px)}
  .cc-txt small{display:block;font-size:19px;margin-top:4px}
  .cc-btns{display:flex;gap:22px;justify-content:center;margin-top:20px;flex-wrap:wrap}
  .cc-pill{display:inline-grid;place-items:center;height:50px;padding:0 38px;border-radius:999px;background:#fff;color:#111!important;font-weight:600;font-size:18px;transition:.25s}
  .cc-pill:hover{background:#e6e6e6}
  .cc-arrow{position:absolute;z-index:3;top:50%;transform:translateY(-50%);width:60px;height:60px;border-radius:50%;border:0;background:#000;color:#fff;display:grid;place-items:center;cursor:pointer;transition:.25s}
  .cc-arrow:hover{background:#333}
  .cc-prev{left:clamp(12px,5.5vw,105px)} .cc-next{right:clamp(12px,5.5vw,105px)}
  .cc-info{position:absolute;z-index:3;bottom:28px;right:clamp(12px,3vw,55px);padding:3px 12px;border-radius:999px;border:1px solid #fff;background:#000;color:#fff;font-size:14px;cursor:pointer}
  .cc-note{position:absolute;z-index:4;right:clamp(12px,3vw,55px);bottom:68px;max-width:320px;padding:12px 14px;background:#fff;color:#111;font-size:14px;border-radius:8px;display:none}
  .cc-note.on{display:block}
  .cc :focus-visible{outline:3px solid #2b7fff;outline-offset:2px}
  @media(max-width:860px){
    .cc-menu,.cc-acc span{display:none}
    .cc-burger{display:block} .cc-acc{margin-left:12px}
    .cc-menu.open{display:flex;flex-direction:column;gap:0;position:absolute;top:80px;left:0;right:0;margin:0;background:#fff;border-bottom:1px solid var(--line)}
    .cc-menu.open a{padding:16px 24px;border:0;border-top:1px solid var(--line)}
    .cc-arrow{width:42px;height:42px}
    .cc-pill{padding:0 26px}
  }
  @media(prefers-reduced-motion:reduce){.cc-slide{transition:none}}

  .cc-skip{position:absolute;left:12px;top:-60px;z-index:100;background:#000;color:#fff;padding:10px 16px;border-radius:6px}
  .cc-skip:focus{top:10px}
  .cc-logo small{display:block;font-size:12px;font-weight:400;letter-spacing:0;color:var(--mute);margin-top:2px}
  .cc-logo span{line-height:1.1}
  .cc-foot{background:#f4f4f4;border-top:1px solid var(--line);padding:40px clamp(16px,7vw,140px);display:flex;align-items:center;gap:20px;flex-wrap:wrap}
  .cc-foot .cc-logo i{width:44px;height:44px}
  .cc-foot p{margin:4px 0 0;color:var(--mute);font-size:15px}
  .cc-foot .cc-foot-nav{margin-left:auto;display:flex;gap:32px;font-size:16px;font-weight:500;color:var(--mute)}
  .cc-foot nav a:hover{color:var(--ink);text-decoration:underline}
  .cc-main:focus{outline:none}
  @media(max-width:860px){.cc-logo small{display:none}.cc-foot .cc-foot-nav{margin-left:0;flex-wrap:wrap;gap:18px}}

  /* ===== BERANDA: satu layar penuh, tanpa scroll ===== */
  body.cc-home{overflow:hidden;height:100vh;height:100dvh}
  body.cc-home .cc-hero{height:calc(100vh - 80px);height:calc(100dvh - 80px);max-height:none;min-height:0}
  body.cc-home .cc-main,body.cc-home .cc-foot{display:none}
  .cc-txt{z-index:5}
  .cc .cc-btns a.cc-pill{display:inline-flex;align-items:center;justify-content:center;height:50px;padding:0 38px;border-radius:999px;background:#fff!important;color:#111!important;font-weight:600;font-size:18px;white-space:nowrap}
  .cc .cc-btns a.cc-pill:hover{background:#e6e6e6!important}
  .cc-quick{position:absolute;z-index:3;left:50%;bottom:30px;transform:translateX(-50%);display:flex;gap:24px;font-size:14px;color:#fff}
  .cc-quick a{text-decoration:underline;text-underline-offset:3px;opacity:.9}
  .cc-quick a:hover{opacity:1}
  .cc-only-m{display:none}
  .cc-txt .cc-desc{display:block;max-width:640px;margin:22px auto 0;font-size:clamp(15px,1.5vw,19px);line-height:1.65;opacity:.95}
  @media(max-height:760px){.cc-txt .cc-desc{display:none}.cc-txt h1{font-size:40px}.cc-txt p{font-size:18px}.cc-txt strong{margin-top:8px;font-size:26px}.cc-txt small{display:none}.cc-btns{margin-top:14px}.cc-pill{height:44px}}
  @media(max-width:860px){.cc-txt{padding:0 64px}.cc-quick{display:none}.cc-only-m{display:block}.cc-menu:not(.open) .cc-only-m{display:none}.cc-btns{gap:12px}.cc .cc-btns a.cc-pill{padding:0 22px;font-size:16px}}

  /* Navbar kaca buram: bening, warnanya mengikuti foto di belakangnya */
  .cc-nav{background:linear-gradient(rgba(0,0,0,.30),rgba(0,0,0,.08));-webkit-backdrop-filter:blur(18px) saturate(160%);backdrop-filter:blur(18px) saturate(160%);border-bottom:1px solid rgba(255,255,255,.22);--ink:#fff;--mute:rgba(255,255,255,.92);text-shadow:0 1px 6px rgba(0,0,0,.35)}
  .cc-nav :focus-visible{outline-color:#fff}
  .cc-nav .cc-logo small{color:rgba(255,255,255,.88)}
  /* Di Beranda, hero naik ke belakang navbar supaya efek blur terlihat pada foto */
  body.cc-home .cc-nav{margin-bottom:-80px}
  body.cc-home .cc-hero{height:100vh;height:100dvh}
  @media(max-width:860px){
    .cc-menu.open{background:rgba(10,18,26,.72);-webkit-backdrop-filter:blur(18px) saturate(160%);backdrop-filter:blur(18px) saturate(160%);border-bottom-color:rgba(255,255,255,.22)}
    .cc-menu.open a{border-top-color:rgba(255,255,255,.2)}
  }
  @supports not ((backdrop-filter:blur(1px)) or (-webkit-backdrop-filter:blur(1px))){.cc-nav{background:rgba(10,18,26,.82)}.cc-menu.open{background:rgba(10,18,26,.95)}}

  /* Jarak bawah blok teks agar tidak menempel ke tautan di dasar hero */
  .cc-txt{padding-bottom:56px}
  @media(max-height:760px){.cc-txt{padding-bottom:36px}}

  /* Animasi kedatangan teks: muncul bertahap setiap slide aktif */
  .cc-slide.on .cc-txt>*{animation:ccTxtIn .8s cubic-bezier(.22,.61,.36,1) both}
  .cc-slide.on .cc-txt>*:nth-child(1){animation-delay:.15s}
  .cc-slide.on .cc-txt>*:nth-child(2){animation-delay:.3s}
  .cc-slide.on .cc-txt>*:nth-child(3){animation-delay:.45s}
  .cc-slide.on .cc-txt>*:nth-child(4){animation-delay:.55s}
  .cc-slide.on .cc-txt>*:nth-child(5){animation-delay:.7s}
  @keyframes ccTxtIn{from{opacity:0;transform:translateY(24px);filter:blur(4px)}to{opacity:1;transform:none;filter:none}}
  @media(prefers-reduced-motion:reduce){.cc-slide.on .cc-txt>*{animation:none}}

  /* Latar slide: foto lokal -> foto kampus (tanpa ilustrasi SVG) */
  .bg1{background-image:url('{{ asset('images/hero1.jpg') }}'),url('https://unsplash.com/photos/kaZHJj-DhC4/download?force=true&w=1920')}
  .bg2{background-image:url('{{ asset('images/hero2.jpg') }}'),url('https://unsplash.com/photos/Dg8WzlOE1as/download?force=true&w=1920')}
  .bg3{background-image:url('{{ asset('images/hero3.jpg') }}'),url('https://images.pexels.com/photos/7972485/pexels-photo-7972485.jpeg?auto=compress&cs=tinysrgb&w=1920')}
  .bg1::after{background:linear-gradient(to top,rgba(0,0,0,.55) 0%,rgba(0,0,0,.35) 55%,rgba(0,0,0,.25) 100%)}
</style>
</head>
<body class="{{ request()->routeIs('home') ? 'cc-home' : '' }}">
    <a href="#konten" class="cc-skip">Lewati ke konten utama</a>

    <header class="cc cc-nav">
        <a href="{{ route('home') }}" class="cc-logo">
            <i aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7-4.6-9.3-9.2C1.1 8.5 3 5 6.4 5c2 0 3.6 1.1 5.6 3 2-1.9 3.6-3 5.6-3C21 5 22.900 8.500 21.300 11.800 19 16.400 12 21 12 21z"/></svg></i>
            <span><strong>CAMPUSCARE</strong><small>Layanan Satgas PPKS Kampus</small></span>
        </a>

        <nav class="cc-menu" id="ccMenu" aria-label="Navigasi utama">
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('tiket') }}">Cek Status</a>
            <a href="{{ route('bantuan') }}">Bantuan</a>
            <a href="{{ route('masuk') }}" rel="nofollow">Portal Petugas</a>
        </nav>

        <a href="{{ route('lapor') }}" class="cc-acc">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
            <span>Buat Laporan</span>
        </a>
        <button class="cc-burger" id="ccBurger" aria-label="Buka menu" aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </button>
    </header>

    {{-- HERO: hanya tampil di Beranda. Satu-satunya direktif tambahan; hapus @if/@endif bila ingin tampil di semua halaman. --}}
    @if(request()->routeIs('home'))
    <div class="cc">
    <section class="cc-hero" aria-roledescription="carousel" aria-label="Sorotan CampusCare">
    <div class="cc-slide bg1 on">
      <div class="cc-txt">
        <h1>CampusCare</h1>
        <p>Ruang aman untuk bersuara dan mendapat pendampingan.</p>
        <strong>Anonim</strong><small>Identitas Anda dijaga oleh Satgas PPKS</small>
        <span class="cc-desc">CampusCare adalah layanan Satgas PPKS kampus untuk menerima laporan kekerasan seksual secara aman dan rahasia. Anda tidak sendirian, dan setiap laporan ditangani dengan serius.</span>
      </div>
    </div>
    <div class="cc-slide bg2">
      <div class="cc-txt">
        <h1>Lapor dengan Aman</h1>
        <p>Ceritakan yang terjadi, kapan pun Anda siap.</p>
        <strong>Tanpa Akun</strong><small>Cukup isi formulir laporan</small>
        <span class="cc-desc">Isi formulir laporan dengan kata-kata Anda sendiri. Anda tidak perlu membuat akun, dan boleh melapor kapan pun merasa siap.</span>
      </div>
    </div>
    <div class="cc-slide bg3">
      <div class="cc-txt">
        <h1>Pantau Laporan Anda</h1>
        <p>Gunakan kode tiket untuk melihat perkembangan.</p>
        <strong>Kode Tiket</strong><small>Simpan kode setelah laporan terkirim</small>
        <span class="cc-desc">Setelah laporan terkirim, Anda mendapat kode tiket. Masukkan kode itu di menu Cek Status untuk melihat perkembangan laporan Anda.</span>
      </div>
    </div>

    <button class="cc-arrow cc-prev" id="ccPrev" aria-label="Slide sebelumnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg></button>
    <button class="cc-arrow cc-next" id="ccNext" aria-label="Slide berikutnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></button>
    <div class="cc-note" id="ccNote" role="status">Foto hanya ilustrasi. Laporan Anda ditangani secara rahasia oleh Satgas PPKS kampus.</div>
    <div class="cc-quick"><a href="{{ route('bantuan') }}">Kontak Darurat</a></div>
    <button class="cc-info" id="ccInfo" aria-label="Keterangan">Info</button>
    </section>
    </div>
    @endif

    <main id="konten" class="cc-main" tabindex="-1">
        @yield('content')
    </main>

    <footer class="cc cc-foot">
        <div class="cc-logo"><i aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7-4.6-9.3-9.2C1.1 8.5 3 5 6.4 5c2 0 3.6 1.1 5.6 3 2-1.9 3.6-3 5.6-3C21 5 22.900 8.500 21.300 11.800 19 16.400 12 21 12 21z"/></svg></i></div>
        <div>
            <strong>CampusCare</strong>
            <p>Kampus yang aman adalah tanggung jawab kita bersama.</p>
        </div>
    </footer>

    <script>
    (function(){
      var m=document.getElementById('ccMenu'),b=document.getElementById('ccBurger');
      b.onclick=function(){var o=m.classList.toggle('open');b.setAttribute('aria-expanded',o)};
    })();
    </script>
    <script>
    (function(){
      var slides=[].slice.call(document.querySelectorAll('.cc-slide'));
      if(!slides.length)return;
      var i=0,DUR=5000,timer=null,hero=document.querySelector('.cc-hero');
      function go(n){i=(n+slides.length)%slides.length;slides.forEach(function(s,k){s.classList.toggle('on',k===i)});restart()}
      function restart(){clearInterval(timer);timer=setInterval(function(){go(i+1)},DUR)}
      document.getElementById('ccPrev').onclick=function(){go(i-1)};
      document.getElementById('ccNext').onclick=function(){go(i+1)};
      document.getElementById('ccInfo').onclick=function(){document.getElementById('ccNote').classList.toggle('on')};
      document.addEventListener('keydown',function(e){if(e.key==='ArrowLeft')go(i-1);if(e.key==='ArrowRight')go(i+1)});
      var x0=null;
      hero.addEventListener('touchstart',function(e){x0=e.touches[0].clientX},{passive:true});
      hero.addEventListener('touchend',function(e){if(x0===null)return;var d=e.changedTouches[0].clientX-x0;if(Math.abs(d)>50)go(i+(d<0?1:-1));x0=null});
      restart();
    })();
    </script>
</body>
</html>