<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Web Programming - Exercise 2</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            max-width: 900px;
            width: 100%;
        }

        h1 {
            text-align: center;
            margin-bottom: 0.5rem;
            font-weight: 800;
            font-size: 2.5rem;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle {
            text-align: center;
            color: #ccc;
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        .tasks {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .task-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 2rem;
            text-decoration: none;
            color: #fff;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .task-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.1);
            border-color: #00c6ff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .task-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #00c6ff, #0072ff);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .task-card:hover::before {
            opacity: 1;
        }

        .task-number {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #00c6ff;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .task-title {
            font-size: 1.4rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .task-desc {
            color: #aaa;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .db-input {
            margin-top: auto;
            display: flex;
            gap: 10px;
        }

        .db-input input {
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 12px;
            border-radius: 5px;
            color: white;
            width: 100%;
            outline: none;
            font-size: 0.9rem;
        }
        
        .db-input input:focus {
            border-color: #00c6ff;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.3s;
            text-align: center;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Exercise 2</h1>
        <p class="subtitle">Advanced Web Programming Tasks</p>

        <div class="tasks">
            <!-- Task 1 -->
            <div class="task-card">
                <div class="task-number">Task 1</div>
                <div class="task-title">Database Backup</div>
                <div class="task-desc">Generate a ZIP backup containing SQL INSERT statements for a specified database.</div>
                <form action="task1.php" method="GET" class="db-input">
                    <input type="text" name="db" value="mysql" placeholder="Database Name">
                    <button type="submit" class="btn">Run</button>
                </form>
            </div>

            <!-- Task 2 -->
            <a href="task2.php" class="task-card">
                <div class="task-number">Task 2</div>
                <div class="task-title">XML Parser</div>
                <div class="task-desc">Parse the LV2.xml file and beautifully display the parsed user profiles.</div>
                <div style="margin-top: auto;">
                    <span class="btn">View Profiles</span>
                </div>
            </a>

            <!-- Task 3 -->
            <a href="task3.php" class="task-card">
                <div class="task-number">Task 3</div>
                <div class="task-title">Secure Upload</div>
                <div class="task-desc">Upload documents safely. Files are automatically encrypted on the server using OpenSSL.</div>
                <div style="margin-top: auto;">
                    <span class="btn">Open Portal</span>
                </div>
            </a>
        </div>
    </div>
</body>
</html>
