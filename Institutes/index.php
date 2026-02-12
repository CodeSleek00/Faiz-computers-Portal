<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Management Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #22a6f2;
            --primary-dark: #0d72bc;
            --primary-soft: #dff2ff;
            --text: #133047;
            --bg: #f5fbff;
            --white: #ffffff;
            --border: #c8e8ff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Poppins", sans-serif;
            color: var(--text);
            background: var(--bg);
        }

        .layout {
            min-height: 100vh;
        }

        .mobile-topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: linear-gradient(120deg, #27b2ff 0%, #1289db 100%);
            color: var(--white);
            box-shadow: 0 8px 24px rgba(16, 100, 158, 0.2);
        }

        .brand {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .menu-btn {
            border: 0;
            border-radius: 8px;
            padding: 9px 11px;
            background: rgba(255, 255, 255, 0.16);
            color: var(--white);
            font-size: 1.05rem;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .menu-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #0f7fca 0%, #0b659f 100%);
            color: var(--white);
            padding: 20px 16px;
            transform: translateX(-100%);
            transition: transform 0.25s ease;
            z-index: 60;
            overflow-y: auto;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 8px 0 32px rgba(8, 57, 90, 0.28);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .sidebar .brand {
            margin-bottom: 24px;
            display: block;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .nav-list {
            list-style: none;
            display: grid;
            gap: 8px;
        }

        .nav-list a {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--white);
            padding: 12px 12px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            transition: background 0.2s ease, transform 0.2s ease;
            font-weight: 500;
        }

        .nav-list a:hover {
            background: rgba(255, 255, 255, 0.22);
            transform: translateX(3px);
        }

        .nav-list i {
            width: 18px;
            text-align: center;
        }

        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(7, 15, 28, 0.45);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease;
            z-index: 55;
        }

        .overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .content {
            padding: 20px 16px;
        }

        .hero-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 12px 30px rgba(20, 111, 175, 0.12);
            max-width: 900px;
        }

        .content h1 {
            margin-bottom: 10px;
            font-size: 1.7rem;
            color: #0b4d7b;
            font-weight: 700;
        }

        .content p {
            color: #31607f;
            margin-top: 8px;
        }

        @media (min-width: 992px) {
            .mobile-topbar {
                display: none;
            }

            .sidebar {
                transform: translateX(0);
            }

            .overlay {
                display: none;
            }

            .content {
                margin-left: var(--sidebar-width);
                padding: 34px;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <header class="mobile-topbar">
            <div class="brand"><i class="fa-solid fa-graduation-cap"></i>Institute Portal</div>
            <button class="menu-btn" id="menuToggle" aria-label="Open Menu" aria-expanded="false" aria-controls="sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
        </header>

        <aside class="sidebar" id="sidebar">
            <span class="brand"><i class="fa-solid fa-graduation-cap"></i>Institute Portal</span>
            <ul class="nav-list">
                <li><a href="#"><i class="fa-solid fa-gauge-high"></i>Dashboard</a></li>
                <li><a href="#"><i class="fa-solid fa-user-graduate"></i>Students</a></li>
                <li><a href="#"><i class="fa-solid fa-chalkboard-user"></i>Faculty</a></li>
                <li><a href="#"><i class="fa-solid fa-book-open"></i>Courses</a></li>
                <li><a href="#"><i class="fa-solid fa-file-signature"></i>Admissions</a></li>
                <li><a href="#"><i class="fa-solid fa-pen-ruler"></i>Exams</a></li>
                <li><a href="#"><i class="fa-solid fa-gear"></i>Settings</a></li>
            </ul>
        </aside>

        <div class="overlay" id="overlay"></div>

        <main class="content">
            <section class="hero-card">
                <h1>Welcome to Institute Management Portal</h1>
                <p>This page uses a premium responsive navigation layout.</p>
                <p>On mobile, the menu opens with the toggle button.</p>
                <p>On desktop, the navigation stays fixed on the left side.</p>
            </section>
        </main>
    </div>

    <script>
        const menuToggle = document.getElementById("menuToggle");
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");

        function closeMenu() {
            sidebar.classList.remove("open");
            overlay.classList.remove("show");
            menuToggle.setAttribute("aria-expanded", "false");
        }

        function openMenu() {
            sidebar.classList.add("open");
            overlay.classList.add("show");
            menuToggle.setAttribute("aria-expanded", "true");
        }

        menuToggle.addEventListener("click", () => {
            const isOpen = sidebar.classList.contains("open");
            if (isOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        overlay.addEventListener("click", closeMenu);

        window.addEventListener("resize", () => {
            if (window.innerWidth >= 992) {
                closeMenu();
            }
        });
    </script>
</body>
</html>
