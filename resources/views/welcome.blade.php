<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records Management System</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #252525; /* Light gray background */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background-color: #14419b;
            box-shadow: 0 1px 2px 0 rgba(255, 255, 255, 0.1);
            padding: 1.5rem 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 600;
        }

        nav a {
            text-decoration: none;
            color: #4b5563;
            margin-left: 1rem;
        }

        nav a:hover {
            color: #111827;
        }

        .nav-signup {
            background-color: #3b82f6;
            color: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
        }

        .nav-signup:hover {
            background-color: #2563eb;
        }

        .hero {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 1rem;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .hero p {
            font-size: 1.125rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .hero-buttons button {
            padding: 0.75rem 2rem;
            border-radius: 0.375rem;
            border: none;
            font-size: 1rem;
            cursor: pointer;
        }

        .hero-buttons .primary-button {
            background-color: #3b82f6;
            color: #ffffff;
        }

        .hero-buttons .primary-button:hover {
            background-color: #2563eb;
        }

        .hero-buttons .secondary-button {
            background-color: #e5e7eb;
            color: #374151;
        }

        .hero-buttons .secondary-button:hover {
            background-color: #d1d5db;
        }

        footer {
            background-color: #14419b;
            padding: 2rem 1rem;
            text-align: center;
        }

        footer p {
            font-size: 0.875rem;
            color: #ffffff;
        }

        @media (min-width: 768px) {
            .hero h1 {
                font-size: 4rem;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src="" style="height: 40px;">
                </div>
                <nav>

                </nav>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div style="border: 1px solid rgb(77, 76, 76); padding: 2rem; border-radius: 20px;">
                <h1 style="color: white;">Records Management System</h1>
                <p></p>
                <div class="hero-buttons">
                    <a href="http://127.0.0.1:8000/rms-admin"> <button class="primary-button">Sign in</button></a>
                    <a href="https://bwc.dole.gov.ph" target="_blank"> <button class="secondary-button">BWC Website</button></a>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 DOLE-Bureau of Working Conditions. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>