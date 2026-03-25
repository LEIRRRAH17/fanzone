

// Switch to register view
function openRegister() {
  document.getElementById('login-panel').style.display  = 'none';
  document.getElementById('register-panel').style.display = 'flex';
  document.title = 'FanZone – Create Account';
}

// Switch back to login view
function openLogin() {
  document.getElementById('register-panel').style.display = 'none';
  document.getElementById('login-panel').style.display    = 'flex';
  document.title = 'FanZone – Login';
}

// Password strength meter
function checkStrength(val) {
  let score = 0;
  if (val.length >= 6)           score++;
  if (val.length >= 10)          score++;
  if (/[A-Z]/.test(val))         score++;
  if (/[0-9]/.test(val))         score++;
  if (/[^A-Za-z0-9]/.test(val))  score++;

  const levels = [
    { width: '20%',  color: '#ef4444', label: 'Weak'        },
    { width: '40%',  color: '#f97316', label: 'Fair'        },
    { width: '60%',  color: '#eab308', label: 'Good'        },
    { width: '80%',  color: '#22c55e', label: 'Strong'      },
    { width: '100%', color: '#10b981', label: 'Very Strong' },
  ];

  const lvl   = levels[Math.min(score, 4)];
  const fill  = document.getElementById('str-fill');
  const label = document.getElementById('str-label');
  if (fill)  { fill.style.width = lvl.width; fill.style.background = lvl.color; }
  if (label) { label.textContent = lvl.label; label.style.color = lvl.color; }
}