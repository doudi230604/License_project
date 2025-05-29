<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root {
      --base-bgcolor: #f8fafc;
      --base-color: #134e4a;
      --base-font-weight: 300;
      --base-font-size: 1rem;
      --base-line-height: 1.5;
      --base-font-family: 'Helvetica Neue', sans-serif;
      --input-placeholder-color: #7e8ba3;
      --grid-max-width: 25rem;
      --grid-width: 100%;
      --link-color: #0d9488;
    }

    * { box-sizing: border-box; }

    html, body {
      height: 100%;
      margin: 0;
      background-color: var(--base-bgcolor);
      color: var(--base-color);
      font-weight: var(--base-font-weight);
      font-size: var(--base-font-size);
      line-height: var(--base-line-height);
      font-family: var(--base-font-family);
    }

    .align {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
    }

    .grid {
      max-width: var(--grid-max-width);
      width: var(--grid-width);
      margin: auto;
    }

    .register {
      text-align: center;
      padding: 4rem 2rem;
      background: #fff;
      border-radius: 1.5rem;
      box-shadow: 0 0 32px #99f6e4;
    }

    .site__logo {
      margin-bottom: 2rem;
    }

    h2 {
      font-size: 2.5rem;
      font-weight: 100;
      margin-bottom: 1rem;
      color: #0f766e;
      text-transform: uppercase;
    }

    .form__field {
      margin-bottom: 1rem;
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 0.7rem 1rem;
      border: 1px solid #99f6e4;
      border-radius: 999px;
      background-color: #f0fdfa;
      color: #134e4a;
      outline: none;
      font-size: 1rem;
      text-align: center;
      background-repeat: no-repeat;
      background-size: 1.5rem;
      background-position: 1rem 50%;
    }

    input::placeholder {
      color: var(--input-placeholder-color);
    }

    input[type="email"] {
      background-image: url('data:image/svg+xml,%3Csvg fill="%230d9488" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"%3E%3Cpath d="M256.017 273.436l-205.17-170.029h410.904l-205.734 170.029zm-.034 55.462l-205.983-170.654v250.349h412v-249.94l-206.017 170.245z"/%3E%3C/svg%3E');
    }

    input[type="password"] {
      background-image: url('data:image/svg+xml,%3Csvg fill="%230d9488" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"%3E%3Cpath d="M195.334 223.333h-50v-62.666c0-61.022 49.645-110.667 110.666-110.667 61.022 0 110.667 49.645 110.667 110.667v62.666h-50v-62.666c0-33.452-27.215-60.667-60.667-60.667-33.451 0-60.666 27.215-60.666 60.667v62.666zm208.666 30v208.667h-296v-208.667h296zm-121 87.667c0-14.912-12.088-27-27-27s-27 12.088-27 27c0 7.811 3.317 14.844 8.619 19.773 4.385 4.075 6.881 9.8 6.881 15.785v22.942h23v-22.941c0-5.989 2.494-11.708 6.881-15.785 5.302-4.93 8.619-11.963 8.619-19.774z"/%3E%3C/svg%3E');
    }

    input[type="submit"] {
      background-color: #0d9488;
      color: #fff;
      font-size: 1.1rem;
      font-weight: bold;
      border: none;
      border-radius: 999px;
      padding: .7rem 1rem;
      width: 100%;
      cursor: pointer;
      box-shadow: 0 2px 8px #99f6e4;
      transition: background-color 0.2s;
    }

    input[type="submit"]:hover {
      background-color: #0f766e;
    }

  </style>
</head>
<body class="align">
  <div class="grid align__item">
    <div class="register">

      <!-- Logo -->
      <svg xmlns="http://www.w3.org/2000/svg" class="site__logo" width="56" height="84" viewBox="77.7 214.9 274.7 412">
        <defs>
          <linearGradient id="a" x1="0%" y1="0%" y2="0%">
            <stop offset="0%" stop-color="#8ceabb"/>
            <stop offset="100%" stop-color="#378f7b"/>
          </linearGradient>
        </defs>
        <path fill="url(#a)" d="M215 214.9c-83.6 123.5-137.3 200.8-137.3 275.9 0 75.2 61.4 136.1 137.3 136.1s137.3-60.9 137.3-136.1c0-75.1-53.7-152.4-137.3-275.9z"/>
      </svg>

      <h2>Login</h2>

      <!-- FORM: Connects to auth.php backend -->
      <form action="auth.php" method="post" class="form">
        <div class="form__field">
          <input type="email" name="email" placeholder="info@mailaddress.com" required>
        </div>
        <div class="form__field">
          <input type="password" name="pswd" placeholder="••••••••••••" required>
        </div>
        <div class="form__field">
          <input type="submit" name="login" value="Login">
        </div>
      </form>

    </div>
  </div>
</body>
</html>
