<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "yared_portfolio";

$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
$conn->query("CREATE DATABASE IF NOT EXISTS `$database`");
$conn->select_db($database);

$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

function clean($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

$status = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["send_message"])) {
    $senderName = trim($_POST["sender_name"] ?? "");
    $senderEmail = trim($_POST["sender_email"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($senderName === "" || !filter_var($senderEmail, FILTER_VALIDATE_EMAIL) || $message === "") {
        $status = "Please complete all fields with a valid email address.";
    } else {
        $stmt = $conn->prepare("INSERT INTO messages (sender_name, sender_email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $senderName, $senderEmail, $message);
        if ($stmt->execute()) {
            header("Location: " . strtok($_SERVER["REQUEST_URI"], "?") . "?sent=1#contact");
            exit;
        } else {
            $status = "Sorry, your message could not be sent. Please try again.";
        }
        $stmt->close();
    }
}

if (isset($_GET["sent"])) {
    $status = "Thanks for saying hi! Your message has been sent.";
}

$page = $_GET["page"] ?? "home";
if (!in_array($page, ["home", "messages"], true)) {
    $page = "home";
}

$messages = [];
if ($page === "messages") {
    $result = $conn->query("SELECT sender_name, sender_email, message, created_at FROM messages ORDER BY id DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>yared melese| Student Developer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            }

        h1, h2, h3, .brand {
            font-family: "Space Grotesk", sans-serif;
        }

        .navbar {
            background: rgba(255,255,255,.92);
            border-bottom: 1px solid var(--line);
        }

        .brand {
            color: var(--ink);
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .nav-link {
            color: var(--muted);
            font-weight: 600;
        }

        .nav-link:hover, .nav-link.active { color: var(--accent); }

        .hero {
            padding: 100px 0 82px;
            background:
                radial-gradient(circle at 85% 20%, rgba(82,103,232,.12), transparent 27%),
                radial-gradient(circle at 12% 90%, rgba(82,103,232,.08), transparent 25%);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: var(--accent);
            background: var(--accent-light);
            padding: 8px 13px;
            border-radius: 30px;
            font-size: .82rem;
            font-weight: 700;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #24b47e;
        }

        .hero h1 {
            max-width: 760px;
            margin: 24px 0 18px;
            font-size: clamp(2.8rem, 7vw, 5.2rem);
            line-height: 1.03;
            letter-spacing: -4px;
            font-weight: 700;
        }

        .hero h1 span { color: var(--accent); }

        .lead-text {
            max-width: 600px;
            color: var(--muted);
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .btn-main {
            background: var(--accent);
            color: white;
            border: 1px solid var(--accent);
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 700;
        }

        .btn-main:hover {
            background: #4054d0;
            border-color: #4054d0;
            color: white;
            background: var(--dark);
            color: white;
            border-radius: 22px;
            padding: 30px;
            box-shadow: 0 22px 55px rgba(17,26,48,.14);
        }

        .hero-card .big-number {
            font-family: "Space Grotesk", sans-serif;
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .hero-card p { color: #b8c1d5; }

        .section { padding: 76px 0; }

        .section-label {
            color: var(--accent);
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 2.8rem);
            font-weight: 700;
            letter-spacing: -1.5px;
            margin: 10px 0 12px;
        }

        .section-intro { color: var(--muted); max-width: 600px; }

        .info-card, .project-card, .contact-card, .message-card {
            height: 100%;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 26px;
        }

        .info-card h3, .project-card h3 { font-size: 1.15rem; font-weight: 700; }

        .info-card p, .project-card p { color: var(--muted); line-height: 1.7; }

        .icon-box {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            background: var(--accent-light);
            color: var(--accent);
            border-radius: 13px;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .project-number {
            color: var(--accent);
            font-family: "Space Grotesk", sans-serif;
            font-weight: 700;
            font-size: .9rem;
        }

        .tag {
            display: inline-block;
            background: #f0f2f7;
            color: #59647a;
            border-radius: 7px;
            padding: 6px 9px;
            margin: 4px 4px 0 0;
            font-size: .76rem;
            font-weight: 700;
        }

        .learning-list {
            list-style: none;
            padding: 0;
            margin: 22px 0 0;
        }

        .learning-list li {
            padding: 15px 0;
            border-bottom: 1px solid var(--line);
            font-weight: 600;
        }

        .learning-list li:last-child { border-bottom: 0; }

        .learning-list span {
            color: var(--accent);
            margin-right: 12px;
        }
        .contact-section { background: #eef1f8; }

        .form-control {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 12px 14px;
            background: #fbfcff;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 .2rem rgba(82,103,232,.12);
        }

        .form-label { font-weight: 700; font-size: .9rem; }

        .message-card { margin-bottom: 16px; height: auto; }
        .message-card p { white-space: pre-wrap; color: var(--muted); margin: 14px 0 0; }
        .message-meta { color: var(--muted); font-size: .85rem; }

        footer {
            padding: 26px 0;
            color: var(--muted);
            border-top: 1px solid var(--line);
            font-size: .9rem;
        }

        @media (max-width: 767px) {
            .hero { padding: 75px 0 60px; }
            .hero h1 { letter-spacing: -2px; }
            .section { padding: 58px 0; }
            .hero-card { margin-top: 18px; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container py-2">
        <a class="navbar-brand brand" href="index.php">yared<span style="color:var(--accent)">.</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                <a class="nav-link <?= $page === "home" ? "active" : "" ?>" href="index.php">Home</a>
                <a class="nav-link" href="<?= $page === "messages" ? "index.php#projects" : "#projects" ?>">Projects</a>
                <a class="nav-link" href="<?= $page === "messages" ? "index.php#contact" : "#contact" ?>">Contact</a>
                <a class="btn btn-main ms-lg-2" href="index.php?page=messages">View messages</a>
            </div>
        </div>
    </div>
</nav>

<?php if ($page === "messages"): ?>
<section class="section">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <div class="section-label">Inbox</div>
                <h1 class="section-title mb-2">Messages received</h1>
                <p class="section-intro mb-0">Messages sent through yared's portfolio.</p>
            </div>
            <a href="index.php" class="btn btn-outline">Back to portfolio</a>
        </div>

        <?php if (count($messages) === 0): ?>
            <div class="contact-card text-center py-5">
                <div class="icon-box mx-auto">✉</div>
                <h3 class="fw-bold">No messages yet</h3>
                <p class="text-secondary mb-0">When someone sends a message, it will appear here.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-9">
                    <?php foreach ($messages as $item): ?>
                        <div class="message-card">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div>
                                    <h3 class="h5 fw-bold mb-1"><?= clean($item["sender_name"]) ?></h3>
                                    <a class="message-meta text-decoration-none" href="mailto:<?= clean($item["sender_email"]) ?>"><?= clean($item["sender_email"]) ?></a>
                                </div>
                                <span class="message-meta"><?= clean($item["created_at"]) ?></span>
                            </div>
                            <p><?= clean($item["message"]) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php else: ?>
<header class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-8">
                <div class="eyebrow"><span class="status-dot"></span> Grade 12 student · Learning every day</div>
                <h1>Hi, I'm yared.<br>I build for the <span>web.</span></h1>
                <p class="lead-text">I'm a Grade 12 student at Kings School, focused on web development. I enjoy turning ideas into clean, useful websites while learning how the web works behind the scenes.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="#projects" class="btn btn-main">Explore my work <span class="ms-2">↗</span></a>
                    <a href="#contact" class="btn btn-outline">Say hello</a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="hero-card">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <span class="small text-uppercase fw-bold" style="letter-spacing:2px;color:#b8c1d5">A little about me</span>
                        <span style="font-size:1.4rem">✳</span>
                    </div>
                    <div class="big-number">12<span style="color:#91a0ff">th</span></div>
                    <p class="mt-2 mb-4">Grade at Kings School</p>
                    <div class="d-flex justify-content-between border-top pt-3" style="border-color:#34405a!important">
                        <span class="small" style="color:#b8c1d5">Main focus</span>
                        <strong>Web development</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="section" id="about">
    <div class="container">
        <div class="section-label">About me</div>
        <h2 class="section-title">Curious mind. Growing skills.</h2>
        <p class="section-intro mb-5">I'm building my skills one project at a time, learning both the design people see and the code that makes a website work.</p>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="info-card"><div class="icon-box">⌘</div>
                    <h3>Who I am</h3>
                    <p>My name is yared. I'm a Grade 12 student at Kings School with a strong interest in creating websites.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-box">◎</div>
                    <h3>My focus</h3>
                    <p>I'm focused on web development, especially building simple, clean, and user-friendly web experiences.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-box">↗</div>
                    <h3>My goal</h3>
                    <p>To keep learning, improve my coding skills, and create projects that solve real problems.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section pt-0" id="projects">
    <div class="container">
        <div class="section-label">Selected work</div>
        <h2 class="section-title">Projects I've built</h2>
        <p class="section-intro mb-5">A couple of projects that show what I've been learning and practising.</p>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="project-card">
                    <div class="d-flex justify-content-between mb-4">
                        <span class="project-number">PROJECT 01</span>
                        <span class="text-secondary">↗</span>
                    </div>
                    <h3>Personal Portfolio</h3>
                    <p>A personal website made to introduce myself, share my work, and practise front-end web development.</p>
                    <div class="mt-4">
                        <span class="tag">HTML</span><span class="tag">CSS</span><span class="tag">JavaScript</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="project-card">
                    <div class="d-flex justify-content-between mb-4">
                        <span class="project-number">PROJECT 02</span>
                        <span class="text-secondary">↗</span>
                    </div>
                    <h3>PHP Portfolio</h3>
                    <p>A portfolio website connected to a MySQL database, with a contact form that saves messages.</p>
                    <div class="mt-4">
                        <span class="tag">PHP</span><span class="tag">MySQL</span><span class="tag">Bootstrap</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section pt-0" id="learning">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div class="section-label">Currently learning</div>
                <h2 class="section-title">Learning the logic behind the websites.</h2>
                <p class="section-intro">I'm working on the basics of backend development and understanding how websites store and use information.</p>
            </div>
            <div class="col-lg-6">
                <div class="info-card">
                    <ul class="learning-list">
                        <li><span>01</span> Backend basics</li>
                        <li><span>02</span> PHP</li>
                        <li><span>03</span> MySQL databases</li>
                        <li><span>04</span> Connecting forms to databases</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section contact-section" id="contact">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <div class="section-label">Get in touch</div>
                <h2 class="section-title">Have something to say?</h2>
                <p class="section-intro">Send me a message. Whether it's feedback, a question, or just a quick hello, I'd be happy to hear from you.</p>
                <div class="mt-4 fw-bold">Samson <span style="color:var(--accent)">·</span> Student Developer</div>
                <div class="text-secondary mt-1">Kings School · Grade 12</div>
            </div>
            <div class="col-lg-7">
                <div class="contact-card">
                    <?php if ($status !== ""): ?>
                        <div class="alert <?= isset($_GET["sent"]) ? "alert-success" : "alert-warning" ?>"><?= clean($status) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="#contact">
                        <div class="mb-3">
                            <label class="form-label" for="sender_name">Your name</label>
                            <input class="form-control" type="text" id="sender_name" name="sender_name" maxlength="100" placeholder="Enter your name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="sender_email">Email address</label>
                            <input class="form-control" type="email" id="sender_email" name="sender_email" maxlength="150" placeholder="you@example.com" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="message">Your message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" placeholder="Hi Samson..." required></textarea>
                        </div>
                        <button class="btn btn-main w-100" type="submit" name="send_message">Send message <span class="ms-2">→</span></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<footer>
    <div class="container d-flex flex-wrap justify-content-between gap-2">
        <span>© <?= date("Y") ?> yared melese. Built while learning.</span>
        <span>Designed with care · Powered by PHP & MySQL</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>