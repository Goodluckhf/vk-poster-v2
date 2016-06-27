<!DOCTYPE html>
<html>
    <head>
        <title>Регистрация</title>

        <link href="//fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {


            }

            .title {
                text-align: center;
                font-size: 24px;
            }
            .body p{
                text-align: left;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">
                    Здравствуйте
                </div>
                <div class="body">
                    <p>
                        Мы получили запрос на регистрацию вашей учетной записи в Постере vk.com. Если вы не отправляли запрос, просто проигнорируйте это письмо.<br>
                    Скопируйте ваш ключ в форму!<br>
                    </p>
                    <b>Ключ: </b><i>{{$token}}</i>


                </div>
            </div>
        </div>
    </body>
</html>
