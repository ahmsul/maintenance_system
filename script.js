document.addEventListener("DOMContentLoaded", function () {
    // ترحيب خفيف
    console.log("👋 مرحبًا بك في لوحة التحكم!");
  
    // إظهار النموذج بتأثير تدريجي
    const form = document.querySelector("form");
    if (form) {
      form.style.opacity = 0;
      form.style.transform = "translateY(30px)";
      setTimeout(() => {
        form.style.transition = "0.6s ease";
        form.style.opacity = 1;
        form.style.transform = "translateY(0)";
      }, 300);
    }
  
    // تفاعل مع الحقول
    const inputs = document.querySelectorAll("input[type='text'], input[type='email'], input[type='password']");
    inputs.forEach(function (input) {
      input.addEventListener("focus", function () {
        input.style.transition = "0.3s ease";
        input.style.borderBottom = "2px solid #2980b9";
        input.style.backgroundColor = "#f0f8ff";
      });
      input.addEventListener("blur", function () {
        input.style.borderBottom = "2px solid darkgray";
        input.style.backgroundColor = "white";
      });
    });
  
    // عند إرسال النموذج
    const submitBtn = document.querySelector("#sbn");
    if (form && submitBtn) {
      form.addEventListener("submit", function (e) {
        let empty = false;
        inputs.forEach(function (input) {
          if (input.value.trim() === "") {
            empty = true;
          }
        });
  
        if (empty) {
          e.preventDefault();
  
          // اهتزاز الزر
          submitBtn.style.animation = "shake 0.4s";
          setTimeout(() => {
            submitBtn.style.animation = "";
          }, 400);
  
          alert("يرجى تعبئة جميع الحقول قبل الإرسال.");
        }
      });
    }
  });
  