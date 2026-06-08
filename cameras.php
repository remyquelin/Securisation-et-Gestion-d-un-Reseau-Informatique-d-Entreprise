<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caméras</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS local -->
    <link rel="stylesheet" href="style.css">

    <style>
       /************** GLOBAL LAYOUT ******************/
body {
    margin: 0;
    font-family: Arial, sans-serif;
    display: flex;
    min-height: 100vh;
}

/************** SIDEBAR ******************/
.sidebar {
    width: 250px;
    flex-shrink: 0;
}

/************** MAIN CONTENT ******************/
.main-content {
    flex: 1;
    padding: clamp(1rem, 3vw, 3rem);
    display: flex;
    flex-direction: column;
}

.header {
    margin-bottom: 2rem;
}

.header h1 {
    font-size: clamp(1.8rem, 3vw, 2.5rem);
    font-weight: 700;
    background: rgb(43 43 43);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    color: #acadae;
    border-bottom: 3px solid #dddddd;
    padding-bottom: 5px;
}

/**************** VIDEO CONTAINER ****************/
#video-container {
    width: 100%;
    max-width: 1200px;
    margin: auto;
}

/* Garde un ratio 16:9 propre */
.video-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
}

#video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
    background-color: black;
}

/**************** RESPONSIVE ****************/

/* Tablette */
@media (max-width: 1024px) {
    .sidebar {
        width: 200px;
    }
}

/* Mobile */
@media (max-width: 768px) {

    body {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
    }

    .main-content {
        padding: 1rem;
    }

    .header h1 {
        font-size: 1.6rem;
    }
}

    </style>
</head>

<body>
    <!-- Sidebar -->
    <div id="sidebar-container"></div>
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <h1>Les Caméras</h1>
            <div class="header-divider"></div>
        </header>

        <video id="video" controls autoplay muted></video>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
const video = document.getElementById('video');
const hlsUrl = 'http://192.168.1.11/hls/playlist.m3u8?t=' + new Date().getTime();

function playHLS() {
    if (Hls.isSupported()) {
        const hls = new Hls();
        hls.loadSource(hlsUrl);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED, () => video.play());
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = hlsUrl;
        video.addEventListener('loadedmetadata', () => video.play());
    } else {
        alert("HLS non supporté !");
    }
}

playHLS();
</script>

</body>

</html>
