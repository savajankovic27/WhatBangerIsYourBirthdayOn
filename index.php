<?php
if (isset($_GET['birthday'])) {
    $birthday = $_GET['birthday']; // Get user input for the birthday 
    $dateFormatted = date("Y-m-d", strtotime($birthday)); // format should be aligning with the API call. 

    // Billboard 100, API key is not matching the birthday though for some reason. 
    // Fix needed. 
    $rapidApiKey = '92c1707143mshceb54317ec00e46p1bb0a0jsnb718704d71b1';
    $billboardApiUrl = "https://billboard-api.p.rapidapi.com/hot-100?date={$dateFormatted}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $billboardApiUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-RapidAPI-Key: $rapidApiKey",
        "X-RapidAPI-Host: billboard-api.p.rapidapi.com"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $chartData = json_decode($response, true);

    if (!empty($chartData) && isset($chartData['content'][0])) {
        $topSong = $chartData['content'][0];
        $songTitle = $topSong['title'];
        $artist = $topSong['artist'];

        // Get YouTube link for the song, supposed to present it on screen. 
        $youtubeApiKey = "AIzaSyAt4Ww383K-31hv4hDSP-inDjyIZLOpmRU";
        $youtubeSearchQuery = urlencode("$songTitle $artist official video");
        $youtubeApiUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=$youtubeSearchQuery&type=video&key=$youtubeApiKey";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $youtubeApiUrl);
        $youtubeResponse = curl_exec($ch);
        curl_close($ch);

        $youtubeData = json_decode($youtubeResponse, true);
        $youtubeVideoId = $youtubeData['items'][0]['id']['videoId'] ?? '';
        $youtubeEmbedUrl = "https://www.youtube.com/embed/$youtubeVideoId";
    } else {
        $error = "No song found for this date.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birthday #1 Song Finder</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">🎶 Birthday #1 Song Finder 🎵</h1>

        <form method="GET" action="index.php" class="mt-4">
            <div class="form-group">
                <label for="birthday">Enter Your Birthday:</label>
                <input type="date" class="form-control" id="birthday" name="birthday" required>
            </div>
            <button type="submit" class="btn btn-primary">Find My Song</button>
        </form>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($songTitle) && isset($artist)): ?>
            <div class="song-card mt-4 text-center">
                <h2><?php echo htmlspecialchars($songTitle); ?></h2>
                <p><strong>Artist:</strong> <?php echo htmlspecialchars($artist); ?></p>

                <?php if (!empty($youtubeVideoId)): ?>
                    <iframe width="560" height="315" src="<?php echo $youtubeEmbedUrl; ?>" frameborder="0" allowfullscreen></iframe>
                <?php else: ?>
                    <p>No YouTube video found.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>