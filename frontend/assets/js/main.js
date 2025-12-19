const apiUrl = 'https://your-api-url.com'; // Global URL







function try_to_log_in() {
    const fullName = document.getElementById('fullName').value.trim();
    const phoneNumber = document.getElementById('phoneNumber').value.trim();
    
    // Check if inputs are valid
    if (!fullName || !phoneNumber) {
        alert('نام و شماره تلفن باید پر شوند.');
        return;
    }

    const data = {
        action: 'login',  // Action for login
        name: fullName,
        phone: phoneNumber
    };

    // Make the POST request
    fetch(`${apiUrl}/login.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(response => response.json())
    .then(data => {
        // Handle the received data
        if (data.success) {
            showToast('ورود موفقیت‌آمیز بود', { duration: 1000 });
            // Redirect or do something based on success
        } else {
            showToast('ورود ناموفق. لطفاً دوباره تلاش کنید.', { duration: 1000 });

        }
    })
    .catch(error => {
        // Handle any error
        console.error('Error:', error);
        showToast('خطا در ارتباط با سرور.', { duration: 1000 });
    });
}

// Toast utility
(function () {
  // ensure a single root
  function getToastRoot() {
    let root = document.getElementById('toast-root');
    if (!root) {
      root = document.createElement('div');
      root.id = 'toast-root';
      document.body.appendChild(root);
    }
    return root;
  }

  // Create and show a toast
  window.showToast = function (message, {
    duration = 2000,
    dark = false,
    id = null, // set to avoid duplicates if you want
  } = {}) {
    const root = getToastRoot();

    // optional: prevent duplicate by id
    if (id && root.querySelector(`.toast[data-id="${id}"]`)) {
      return;
    }

    const el = document.createElement('div');
    el.className = 'toast';
    if (dark) el.classList.add('dark');
    if (id) el.dataset.id = id;
    el.textContent = message;

    root.appendChild(el);

    // fade in
    requestAnimationFrame(() => el.classList.add('show'));

    // fade out + remove
    const hide = () => {
      el.classList.remove('show');
      setTimeout(() => el.remove(), 220);
    };
    setTimeout(hide, duration);

    return el;
  };
})();

// loader.js
(function (global) {
  const DEFAULT_ID = "appLoader";

  function getEl(id = DEFAULT_ID) {
    const el = document.getElementById(id);
    if (!el) throw new Error(`Loader element not found. Expected id="${id}"`);
    return el;
  }

  function show({ id = DEFAULT_ID } = {}) {
    const el = getEl(id);
    el.classList.add("is-active");
    el.setAttribute("aria-hidden", "false");
  }

  // Fades out, then disables interactions (via CSS class removal)
  function hide({ id = DEFAULT_ID, fadeMs = 280 } = {}) {
    const el = getEl(id);

    // if already hidden, do nothing
    if (!el.classList.contains("is-active")) return;

    el.classList.remove("is-active");
    el.setAttribute("aria-hidden", "true");

    // In case you want to fully remove it from DOM after fade:
    // setTimeout(() => el.remove(), fadeMs);
  }

  global.Loader = { show, hide };
})(window);
function showLegacyImage() {
  const legacyImage = document.querySelector('.legacy-img');
  
  // Show legacy image with fade-in effect
  setTimeout(() => {
    legacyImage.style.opacity = 1;
  }, 200); 

  // Hide legacy image after 2 seconds
  setTimeout(() => {
    legacyImage.style.opacity = 0;
    setTimeout(() => {
      const story = document.querySelector('.story');
      story.style.display = 'none'; // Show the story section
     }, 1000); 
  
  }, 3000); 
}
function showPersonImages() {
  const personImages = document.querySelectorAll('.person-img');
  
  // Show person images one by one with delays
  personImages.forEach((img, index) => {
    setTimeout(() => {
      img.style.opacity = 1;
    //   img.classList.add('animate-wipe');  
    }, 1000 * (index + 2));  // Delay for each person
  });

  // After all images are shown, wipe person images one by one
  setTimeout(() => {
    personImages.forEach((img, index) => {
      setTimeout(() => {
        img.style.opacity = 0; // Fade out images
      }, 1000 * (index + 1));
    });
  }, 700000);  // Delay the wipe to let all images show
}
function showStorySection() {
  const story = document.querySelector('.story');
  story.style.display = 'flex'; // Show the story section
  
  showLegacyImage();
  // Call the functions to handle legacy image and person images
//   setTimeout(() => {
//      showPersonImages();
//    }, 500); 
}
function showIntroSection() {
    const introSection = document.querySelector('.intro_section');
    introSection.style.display = 'flex';  // Show the section
    setTimeout(() => {
        introSection.style.opacity = 1;  // Fade in by changing opacity
    }, 20);  // Slight delay to ensure the display change takes effect
}
function hideIntroSection() {
    const introSection = document.querySelector('.intro_section');
    introSection.style.opacity = 0;  // Fade out by changing opacity
    setTimeout(() => {
        introSection.style.display = 'none';  // Hide the section after fading out
    }, 1000);  // Delay to match the opacity transition time
}
// Function to convert Persian numerals to Arabic numerals
function convertPersianToArabic(persianNum) {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    const arabicDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    return persianNum.split('').map(function(char) {
        const index = persianDigits.indexOf(char);
        return index !== -1 ? arabicDigits[index] : char;
    }).join('');
}

// Function to validate the phone number
function validatePhoneNumber(event) {
    event.preventDefault();
    const phoneInput = document.getElementById('phoneNumber');
    let phoneNumber = phoneInput.value;

    // Convert Persian digits to Arabic digits
    phoneNumber = convertPersianToArabic(phoneNumber);

    // Validate the phone number using a regular expression
    const phonePattern = /^09\d{9}$/;

    if (!phonePattern.test(phoneNumber)) {
        // Raise an error and change the title of the input
        phoneInput.setCustomValidity('شماره تلفن باید با ۰۹ شروع شود و ۱۱ رقم باشد.');
        phoneInput.reportValidity(); // Trigger the form's built-in validation to show the error message
    } else {
        // Reset the validity and clear any previous error
        phoneInput.setCustomValidity('');
        try_to_log_in();
    }
}

// Add event listener for input change (optional)
document.getElementById('phoneNumber').addEventListener('input', function() {
    this.setCustomValidity('');  // Reset the custom validity message on input change
});

function show_login_page() {

    document.getElementById('anar_div').style.display = 'none';
    document.getElementById('nex_btn_intro_div').style.display = 'none';
    document.getElementById('intro_page1').style.display = 'none';
    document.getElementById('intro_page2').style.display = 'block';
    setTimeout(() => {
        // introSection.style.display = 'none';  // Hide the section after fading out
    }, 1000);  // Delay to match the opacity transition time
}




// setTimeout(Loader.show, 10); // Show the loader after 1 second

document.addEventListener("DOMContentLoaded", function () {
    const continueBtn = document.getElementById('continueBtn');
    setTimeout(() => {
    // continueBtn.click();  
    }, 500);


    continueBtn.addEventListener('click', function() {
        continueBtn.disabled = true;  // Disables the button
        Loader.show()
        setTimeout(Loader.hide, 900); // Show the loader after 1 second
        setTimeout(show_login_page, 300); // Show the loader after 1 second
    });



    // Show loader, then hide it after 3 seconds
    // setTimeout(Loader.hide, 1500); // Hide the loader after 4 seconds

    // // After the loader hides, show the story section with animations
    // setTimeout(showStorySection, 1500);  // took 3 sec // Adjust to fit loader hide time

    // Hide the intro section after 5 seconds for demonstration
    // setTimeout(showIntroSection, 5500);
    setTimeout(showIntroSection, 100);
    // // //  setTimeout(hideIntroSection, 5000);



    document.getElementById('phoneNumber').addEventListener('input', function() {
    const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    this.value = this.value.replace(/[0-9]/g, function(match) {
        return persianNumbers[parseInt(match)];
    });
    });

});
