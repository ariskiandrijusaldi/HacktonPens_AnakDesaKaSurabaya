
/* =========================================
   SIDEBAR DASHBOARD CAMPUSCARE
   ========================================= */

.sidebar {
    display: flex;
    flex-direction: column;

    width: 250px;
    min-width: 250px;
    height: 100vh;

    position: sticky;
    top: 0;
    align-self: flex-start;

    overflow-x: hidden;
    overflow-y: auto;

    background: #075653;
    color: #ffffff;

    transition: width 0.3s ease, background 0.3s ease;
    box-sizing: border-box;
}

.sidebar *,
.sidebar *::before,
.sidebar *::after {
    box-sizing: border-box;
}

/* Navigasi sidebar */
.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 18px 12px;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 12px 14px;
    border-radius: 9px;

    color: #d8efeb;
    text-decoration: none;
    font-size: 14px;
    line-height: 1.4;

    transition: background 0.2s ease, color 0.2s ease;
}

.sidebar-nav a:hover,
.sidebar-nav a.active {
    background: #12847d;
    color: #ffffff;
}

/* Label grup menu */
.sidebar-label {
    padding: 14px 14px 4px;

    color: #a9d5ce;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Bagian bawah sidebar */
.sidebar-bottom {
    margin-top: auto;
    padding: 15px 12px;

    border-top: 1px solid rgba(255, 255, 255, 0.15);
}

.sidebar-bottom a {
    display: block;
    padding: 11px 14px;

    color: #ffffff;
    text-decoration: none;
    border-radius: 8px;

    transition: background 0.2s ease;
}

.sidebar-bottom a:hover {
    background: #12847d;
}

/* =========================================
   RESPONSIVE TABLET DAN MOBILE
   ========================================= */

@media (max-width: 760px) {
    .sidebar {
        display: none;
    }

    /* Sidebar tampil saat kelas mobile-open ditambahkan */
    .sidebar.sidebar-mobile-open {
        display: flex;
        flex-direction: column;

        position: relative;
        top: auto;

        width: 100%;
        min-width: 0;
        height: auto;
        max-height: 80vh;

        overflow-x: hidden;
        overflow-y: auto;
        z-index: 100;

        border-radius: 0 0 12px 12px;
    }

    .sidebar.sidebar-mobile-open .sidebar-nav {
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        width: 100%;
        padding: 12px;
    }

    .sidebar.sidebar-mobile-open .sidebar-nav a {
        width: 100%;
    }

    .sidebar.sidebar-mobile-open .sidebar-bottom {
        display: block;
        margin-top: auto;
        padding: 12px;
    }

    .sidebar.sidebar-mobile-open .sidebar-label {
        display: block;
    }
}