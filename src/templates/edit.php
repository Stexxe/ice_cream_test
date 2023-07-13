<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
    <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
    <title>Edit <?= $name ?></title>
</head>
<body>
<h1 class="p-3"><?= $title ?></h1>
<form class="p-3" action="" method="post">
    <div class="form-row mb-3">
        <div class="form-group">
            <textarea class="form-control" name="content" id="content"><?= $content ?></textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save</button>
</form>
<script>
    const simplemde = new SimpleMDE({ element: document.getElementById("content") });
</script>
</body>
</html>

