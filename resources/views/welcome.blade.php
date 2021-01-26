<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/css/bootstrap.min.css" integrity="sha384-AysaV+vQoT3kOAXZkl02PThvDr8HYKPZhNT5h/CXfBThSRXQ6jW5DO2ekP5ViFdi" crossorigin="anonymous">

    <link rel="stylesheet" href="{{asset('css/flex.css')}}">
    <link rel="stylesheet" href="{{asset('css/preloader.css')}}">
</head>

<body>
<header>
    <h1>L.A.MASTERS</h1>
    <nav>
        <ul class="nav_links">
            <li>
                <a href="#">MAIN</a>
            </li>
            <li>
                <a href="#">TOP 10</a>
            </li>
            <li>
                <a href="#">GRAPHIC</a>
            </li>
        </ul>
    </nav>
</header>
<div class="wrapper">
    <div id="items" class="cards_wrap">
        <div id="items-preloader" class="preloader">
            <div class="loader"></div>
            <p>Обробка результатів...</p>
        </div>
        <script>
            async function getProducts(){
                var items = document.querySelector('#items');

                var data = await fetch('http://hakaton/products');
                var products = await data.json();

                console.log(products);

                products.data.forEach((product) => {
                    items.innerHTML += `
                            <div class="card_item">
                                <div class="card_inner">
                                    <div class="card_top">
                                        <img src="${product.img}" alt="car" />
                                    </div>
                                    <div class="card_bottom">
                                        <div class="card_category">
                                            ${product.brand}
                                        </div>
                                        <div class="card_category">
                                            <p>${product.price_per_one} грн <i>${product.weight_per_one}</i></p>
                                        </div>
                                        <div class="card_info">
                                            <a href="${product.src}" class="title">${product.title}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                })

                var preloader = document.querySelector('#items-preloader')
                if (!preloader.classList.contains('done'))
                    preloader.classList.add('done')
            }

            getProducts();
        </script>
    </div>
</div>

<script type="text/javascript">

    function SideBar_ShowHide()
    {
        var _sb = document.getElementById("SideBar");
        var _width = 'auto';

        if(_sb.style.width == _width) {
            _sb.style.width = '0px';
            return;
        }
        _sb.style.width = _width;
    };

</script>

<div id="SideBar">
    <div id="SideBarMenu">
{{--        <div class="row">--}}
{{--            <div class="col text-white">--}}
{{--                <form method="get">--}}
{{--                    <h5>sdf</h5>--}}
{{--                    <input type="text" class="form-control">--}}
{{--                </form>--}}
{{--            </div>--}}
{{--        </div>--}}
        <form method="get" class="cl1">
            <div class="row">
                <h5>Магазин:</h5>
                <select name="form-control" id="exampleFormControlSelect1" class="bla">
                    <option selected>Всі</option>
                    <option value="Metro">Metro</option>
                    <option value="Novus">Novus</option>
                    <option value="Achan">Achan</option>
                </select>
            </div>

            <div class="row">
                <h5>Ціна</h5>
                <div class="col">
                    <input type="text" class="form-control" placeholder="Від">
                </div>
                <div class="col">
                    <input type="text" class="form-control" placeholder="До">
                </div>
            </div>

            <div class="row">
                <h5>Пошук:</h5>
                <input type="text" class="form-control" id="inputSearch" placeholder="Назва товару">
            </div>
            <input type="submit" class="btn btn-success form-control mt-1" value="Відобразити">
        </form>
    </div>
    <div id="SideBarText" onclick="SideBar_ShowHide();">Фільтри</div>
</div>

</body>

</html>
