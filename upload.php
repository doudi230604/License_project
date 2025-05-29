<!DOCTYPE html>
<html>
<head>
	<title>Slide Navbar</title>
	<link rel="stylesheet" type="text/css" href="slide navbar style.css">
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&display=swap" rel="stylesheet">
</head>
<style>
/* Match index.php color scheme */
:root {
  --primary: #006d5b;
  --primary-light: #3aa89b;
  --primary-dark: #004d43;
  --primary-bg: #ecf7f5;
  --primary-hover: #d2efec;
  --accent: #007c91;
  --accent-light: #33aab5;
  --text-primary: #123c38;
  --text-secondary: #a0dbd3;
  --border-color: #b8dcd7;
  --glass-bg: rgba(255, 255, 255, 0.65);
  --glass-blur: blur(14px);
  --transition-fast: 0.2s ease-in-out;
  --transition-slow: 0.4s ease;
}

body {
  margin: 0;
  padding: 0;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: 'Jost', sans-serif;
  background: linear-gradient(135deg, var(--primary-light), var(--accent-light));
  background-size: 150% 150%;
  animation: gradientShift 8s ease infinite;
}

@keyframes gradientShift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

.main {
  width: 380px;
  background: var(--glass-bg);
  border-radius: 18px;
  padding: 2.5rem 2rem;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
  transition: var(--transition-slow);
}

.signup {
  width: 100%;
}

label {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  text-align: center;
  display: block;
  margin-bottom: 1.5rem;
  letter-spacing: -0.5px;
  transition: color var(--transition-fast);
}

input[type="email"],
input[type="password"] {
  width: 100%;
  padding: 0.8rem 1rem;
  margin-bottom: 1.2rem;
  font-size: 1rem;
  border: 1px solid var(--border-color);
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.6);
  color: var(--text-primary);
  outline: none;
  transition: all var(--transition-fast);
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
}

input::placeholder {
  color: #888;
  opacity: 0.6;
}

input[type="email"]:focus,
input[type="password"]:focus {
  border-color: var(--primary);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(0, 109, 91, 0.15);
}

button {
  width: 100%;
  height: 48px;
  background: var(--primary-dark);
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  box-shadow: 0 6px 20px rgba(0, 109, 91, 0.2);
  transition: background var(--transition-fast), transform 0.1s;
}

button:hover {
  background: var(--primary);
  transform: translateY(-2px);
}

button:active {
  transform: translateY(1px);
}

#chk {
  display: none;
}

/* Remove old login/signup transitions for simplicity */
</style>
<body>
  <div class="main">
    <div class="signup">
      <form method="POST" action="auth.php">
        <label for="chk" aria-hidden="true">Login</label>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="pswd" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
      </form>
    </div>
  </div>
</body>
</html>