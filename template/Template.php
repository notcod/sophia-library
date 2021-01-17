<!DOCTYPE html>
<html>

<head>
    <title><?= SITENAME . " - " . $data['title'] ?></title>
    <meta charset="utf-8">
    <meta name="author" content="https://github.com/notcod/sophia">
    <meta name="description" content="<?= $data['description'] ?>">
    <meta name="keywords" content="<?= $data['keywords'] ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0c6cf2" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="icon" type="image/png" href="//cdn.sophiaphp.com/img/favicon.ico" />
    <link rel="icon" type="image/png" href="<?= IMGROOT ?>/assets/img/favicon.ico" />
    <?php \Sophia\Stylesheet::page($data); ?>
</head>

<body>
    <?php content($data['view'] . '.php', $data); ?>
    <?php \Sophia\Javascript::page($data); ?>
</body>

</html>