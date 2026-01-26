<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Attachment Image</title>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .image-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        img {
            max-width: 90vw;
            max-height: 90vh;
            display: block;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="image-container">
        <img src="{{ $imagePath }}" alt="Attachment Image">
    </div>
</body>
</html>
