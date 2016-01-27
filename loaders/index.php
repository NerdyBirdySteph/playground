<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <base href="http://localhost/playground/<?php echo basename(__DIR__) ; ?>/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS only loaders</title>
    <link href="css/main.css" rel="stylesheet" />
</head>
<body>
    
    <table>
        <thead>
            <tr>
                <th>Gif</th>
                <th>Css</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><img src="inspiration/128x128/Preloader_1/Preloader_1.gif"></td>
                <td>
                    <span class="loader-wrapper">
                        <div id="loader-1">
                            <span class="loader-1-dot-1"></span>
                            <span class="loader-1-dot-2"></span>
                            <span class="loader-1-dot-3"></span>
                            <span class="loader-1-dot-4"></span>
                        </div>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>

    <script src="js/main.js"></script>
</body>