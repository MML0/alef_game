const apiUrl = 'http://127.0.0.1:3000/backend'; // Global URL


const loaderImages = [
    'assets/img/legacy.png',
    'assets/img/baner.png',
    'assets/img/BG_anaar.png',
    'assets/img/cloud.png',
];
document.addEventListener("DOMContentLoaded", function () {
    const continueBtn = document.getElementById('continueBtn');
    //  همکار عزیز، 
    continueBtn.addEventListener('click', function() {
        continueBtn.disabled = true;  // Disables the button
        Loader.show()
        check_is_game_ready()
        setTimeout(Loader.hide, 900); // Show the loader after 1 second
        setTimeout(() => {
            continueBtn.disabled = false;  
        }, 900);
    });


    const token = localStorage.getItem('user_token');

    if (token) {
        console.log("User token found:", token);
        setTimeout(Loader.hide, 1500); 
        // setTimeout(showIntroSection, 2500); // remove this 
        // skip the intro jump to next question 
        get_next_question(token)

    } else {
        console.log("No user token found.");
        loadImages(() => {
          setTimeout(Loader.hide, 800); 
          setTimeout(showStorySection, 800);  // legecy took 3 sec // Adjust to fit loader hide time
          setTimeout(showIntroSection, 4500);// Show rules section after loader animation ends
        });
}


    document.getElementById('phoneNumber').addEventListener('input', function() {
    const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    this.value = this.value.replace(/[0-9]/g, function(match) {
        return persianNumbers[parseInt(match)];
    });
    });

});
function show_score_board (score) {
  console.log(score);
}
function get_next_question(user_token) {
    hideIntroSection()
    const data = {
        token: user_token,
    };

    // Make the POST request
    fetch(`${apiUrl}/get_next_question.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data), // Ensure you are sending this as a JSON string
    })
    .then(response => response.json())
    .then(data => {
        // Handle the received data
        if (data.status === 'success') {
            if (data.message === 'all questions answered') {
                // Show the score board if all questions are answered
                show_score_board(data.score);
            } else {
                // Otherwise, show the next question
                if (data.remaining_seconds==0){
                    showToast('times up', { duration: 2000 });
                    show_score_board(data.score);
                }else{
                  show_question(data.current_question, data.question_text, data.answers,data.remaining_seconds);
                }
            }
        } else {
            // Handle specific error messages from the backend
            if (data.message === 'game not started') {
                showToast('لطفاً منتظر بمانید تا بازی شروع شود.', { duration: 2000 });
            } else {
                // General error message for other cases
                setTimeout(showIntroSection, 1500);
                showToast('ورود ناموفق. لطفاً دوباره تلاش کنید.', { duration: 1000 });
            }
        }
    })
    .catch(error => {
        // Handle any error
        console.error('Error:', error);
        showToast('خطا در ارتباط با سرور.', { duration: 1000 });
    });
}
let remaining_sec = 60*10 ;

function show_question(current_number,question_text,answers,remaining_seconds) {
  document.getElementById('question_container').style.display = 'block';
  setTimeout(() => {document.getElementById('question_container').style.opacity = 1;  }, 200);
  // resetTimer()
  document.getElementById('question_text_p').textContent = question_text;
  document.querySelector('.question_number_p').textContent = convertToPersian(current_number.toString());            
  document.getElementById('answer_text1').textContent = answers[0];

  console.log(current_number,question_text,answers) ;
  startTimer(remaining_seconds)
  
  // setTimeout(() => {
  //   startTimer(10*60)
  // }, 400);

}


// Function to convert English numbers to Persian
function convertToPersian(number) {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return number.toString().replace(/[0-9]/g, (digit) => persianDigits[digit]);
}
let minutes = 0;
let seconds = 0;
let hundredths = 0;
let startTime = 0; // Stores the timestamp when the timer starts
let timerInterval = null;  // To store the interval ID


// Update the timer on the screen
function updateTimer() {
    document.querySelector('.min').textContent = convertToPersian(minutes);
    document.querySelector('.sec').textContent = convertToPersian(seconds.toString().padStart(2, '0'));
    document.querySelector('.th100').textContent = convertToPersian(hundredths.toString().padStart(2, '0'));
}


// Convert total seconds into minutes, seconds, and hundredths
function convertTime(totalSeconds) {
    minutes = Math.floor(totalSeconds / 60);
    seconds = totalSeconds % 60;
    hundredths = 0; // Reset hundredths at the start
    updateTimer();
}

// Start the countdown timer with dynamic starting time (in seconds)
function startTimer(startingSeconds) {
    if (timerInterval !== null) return;  // Check if the timer is already running

    // Record the start time using performance.now() for better accuracy
    startTime = performance.now();

    // Set the initial timer values based on the starting seconds
    convertTime(startingSeconds);

    // Update the timer based on real elapsed time
    timerInterval = setInterval(() => {
        const elapsedTime = performance.now() - startTime;  // Elapsed time in milliseconds
        const elapsedSeconds = Math.floor(elapsedTime / 1000);  // Convert milliseconds to seconds

        let totalSeconds = startingSeconds - elapsedSeconds;  // Subtract elapsed time from total time

        if (totalSeconds <= 0) {
            totalSeconds = 0;  // Ensure it doesn't go below zero
            stopTimer();  // Stop the timer when time reaches zero
            setTimeout(() => { document.querySelector('.th100').textContent = convertToPersian('00'); }, 100);
        }

        // Calculate minutes, seconds, and hundredths
        minutes = Math.floor(totalSeconds / 60);
        seconds = totalSeconds % 60;
        hundredths = Math.floor((elapsedTime % 1000) / 10); // Show hundredths of a second

        updateTimer();
    }, 10); // Update every 10 milliseconds to display hundredths
}


// Stop the countdown timer
function stopTimer() {
    clearInterval(timerInterval);
    timerInterval = null;
}

// Reset the countdown timer to initial values
function resetTimer() {
    minutes = 10;
    seconds = 0;
    hundredths = 0;
    stopTimer();  // Stop the timer when reset
    updateTimer();
    stopTimer();  // Stop the timer when reset
}



// Function to load images into the loader section and handle the loading state
function loadImages(callback) {
    const loaderContainer = document.createElement('div');
    loaderContainer.id = 'loader-container';
    document.body.appendChild(loaderContainer); // Append it to the body

    let loadedImages = 0;
    const totalImages = loaderImages.length;
    let timeoutReached = false;

    // Set a timeout to ensure the loading does not exceed 3 seconds
    const timeout = setTimeout(() => {
        timeoutReached = true;
        console.log("Timeout reached, proceeding anyway.");
        callback();  // Proceed after timeout
    }, 3000);  // 3-second timeout

    loaderImages.forEach(src => {
        const img = new Image();  // Create a new image object
        img.src = src;
        img.alt = src.split('/').pop();  // Use the image file name as the alt text
        img.style.display = 'none'; // Initially hide the images
        img.style.width = '0'; // Set the width to 0
        img.style.position = 'absolute'; // Hide it offscreen or in a dummy place
        img.onload = () => {
            loadedImages++;
            console.log(`${img.src} loaded`);
            if (loadedImages === totalImages && !timeoutReached) {
                clearTimeout(timeout);  // Clear the timeout if all images are loaded
                addDummyText(callback);  // Add dummy text and proceed
            }
        };
        img.onerror = () => {
            console.error(`Failed to load image: ${src}`);
            loadedImages++;  // Still count as loaded to proceed
            if (loadedImages === totalImages && !timeoutReached) {
                clearTimeout(timeout);  // Clear the timeout if all images are loaded
                addDummyText(callback);  // Add dummy text and proceed
            }
        };
        loaderContainer.appendChild(img);
    });
}
// Function to add dummy text to ensure font gets loaded
function addDummyText(callback) {
    const testElement = document.createElement('span');
    testElement.innerText = 'dummy text';
    
    // Apply inline styles for positioning and visibility
    testElement.style.fontFamily = 'Modam';  // Set to the font you want
    testElement.style.position = 'absolute';
    testElement.style.visibility = 'hidden'; // Keep it hidden off-screen
    testElement.style.fontSize = '100px'; // Increase size to ensure font loads
    testElement.style.whiteSpace = 'nowrap'; // Prevent wrapping
    testElement.style.padding = '0';
    testElement.style.margin = '0';
    testElement.style.top = '-9999px'; // Move it far off-screen
    testElement.style.left = '-9999px'; // Move it far off-screen
    document.body.appendChild(testElement);

    // After adding dummy text, directly proceed to callback
    setTimeout(() => {
        console.log('Proceeding with the callback, regardless of font load state.');
        document.body.removeChild(testElement);  // Remove the test element
        callback();  // Proceed to callback
    }, 100);  // Just wait a short time to ensure the dummy text is added
}
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
        phone_number: convertPersianToArabic(phoneNumber) // Ensure this is the correct phone number format
    };

    // Make the POST request
    fetch(`${apiUrl}/login.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data), // Ensure you are sending this as a JSON string
    })
    .then(response => response.json())
    .then(data => {
        // Handle the received data
        if (data.status === 'success') {
            showToast('ورود موفقیت‌آمیز بود', { duration: 1000 });
            Loader.show()
            localStorage.setItem('user_token', data.token);
            localStorage.setItem('current_question', data.current_question);
            get_next_question(data.token);
            setTimeout(Loader.hide, 2000); // Show the loader after 1 second
            // Redirect or do something based on success
        } else {
            // Handle specific error messages from the backend
            if (data.message === 'Phone number is required') {
                showToast('شماره تلفن الزامی است.', { duration: 1000 });
            } else if (data.message === 'User not found') {
                showToast('شماره تلفن یافت نشد.', { duration: 1000 });
            } else {
                // General error message for other cases
                showToast('ورود ناموفق. لطفاً دوباره تلاش کنید.', { duration: 1000 });
            }
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
function check_is_game_ready() {
    // Make the POST request to get the game status
    fetch(`${apiUrl}/get_game_status.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({}) // No data needed in this request
    })
    .then(response => response.json())
    .then(data => {
        // Handle the received data
        if (data.status === 'success') {
            // Check game status
            console.log(data);
            
            switch (data.game_status) {
                case 'not_started':
                    // showToast('منتظر بمانید...', { duration: 1500 });
                    setTimeout(Loader.hide, 300); 
                    setTimeout(() => {
                      showToast('بازی در ساعت ۱۵ شروع می‌شود. منتظر بمانید...', { duration: 1500 });
                    }, 500);

                    break;
                case 'ongoing':
                    setTimeout(show_login_page, 300); // Show the loader after 1 second
                    break;
                case 'completed':
                    showToast('بازی به پایان رسید.', { duration: 1500 });
                    break;
                default:
                    showToast('وضعیت بازی نامشخص است.', { duration: 1000 });
                    break;
            }
        } else {
            // Handle error from the server
            showToast('خطا در دریافت وضعیت بازی.', { duration: 1000 });
        }
    })
    .catch(error => {
        // Handle any error
        console.error('Error:', error);
        showToast('خطا در ارتباط با سرور.', { duration: 1000 });
    });
}
setTimeout(Loader.show, 10); // Show the loader after 1 second

