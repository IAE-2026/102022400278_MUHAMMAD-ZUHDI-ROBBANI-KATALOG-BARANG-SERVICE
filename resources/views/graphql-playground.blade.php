<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GraphQL Playground - Service A</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/graphql-playground-react/build/static/css/index.css">
</head>
<body>
<div id="root"></div>
<script src="https://cdn.jsdelivr.net/npm/graphql-playground-react/build/static/js/middleware.js"></script>
<script>
    window.addEventListener('load', function () {
        GraphQLPlayground.init(document.getElementById('root'), {
            endpoint: '/graphql'
        });
    });
</script>
</body>
</html>
