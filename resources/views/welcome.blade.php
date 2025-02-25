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
            background-color: #f4f4f4;
            color: #333;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        h1 {
            margin-bottom: 10px;
        }

        .container {
            max-width: 960px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .feature {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .feature h2 {
            margin-bottom: 10px;
        }

        .cta-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        footer {
            background-color: #333;
            color: #fff;
            padding: 10px;
            text-align: center;
        }

        /* Responsive adjustments (example) */
        @media (max-width: 768px) {
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <header>
        <section id="cta" style="text-align: right;">
            <a href="https://bwc.dole.gov.ph" class="cta-button">BWC Website</a>
        </section>
    </header>

    <div class="container">
        <section id="overview">
        </section>

        <header>
            <h1>Records Management System</h1>
        </header>

        <section id="features">
        </section>

        <section id="cta" style="text-align: center;">
            <a href="admin" class="cta-button">Sign in</a>
        </section>
    </div>

    <footer style="position: fixed; bottom: 0; width: 100%;">
        <p>&copy; 2025 DOLE-BWC. All rights reserved.</p>
    </footer>

</body>
</html>