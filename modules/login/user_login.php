<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../../styles.css" type="text/css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>

<body>
  <header>
    <div class="header-container">
      <div class="sd-logo-container">
        <a href="https://swarnodigital.in/">
          <img class="sd-logo" src="../../images/sd-logo.webp" />
        </a>
      </div>
    </div>
  </header>
  <div class="login-container">
    <div class="login-header">Login</div>
    <form method="post" action="login" class="login-form" novalidate>
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" class="form-control<?php echo isset ($_GET["error"]) ? " is-invalid" : ""; ?>"
          id="username" value="<?php echo $_POST["username"] ?? ""; ?>" autocomplete="on" required />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <input type="password" name="password"
            class="form-control<?php echo isset ($_GET["error"]) ? " is-invalid" : ""; ?>" id="password" required />
          <span class="input-group-text btn-visibility" onclick="showPassword(this.firstElementChild)">
            <i class="bi bi-eye-slash"></i>
          </span>
        </div>
        <?php if (isset ($_GET["error"])) { ?>
          <div class="invalid-feedback">
            <?php echo $_GET["error"] ?? ""; ?>
          </div>
        <?php } ?>
      </div>
      <button type="submit" class="btn btn-success btn-login" id="submit-btn">Login</button>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
  <script>
    const passwordField = document.getElementById("password");
    function showPassword(toggleIcon) {
      const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
      passwordField.setAttribute("type", type);
      toggleIcon.classList.toggle("bi-eye");
    }
  </script>
</body>

</html>