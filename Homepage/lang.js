function switchLang() {
    let lang = document.getElementById("langSwitcher").value;
    const logoImg = document.getElementById("headerLogo");

    if (lang === "ar") {        document.documentElement.lang = "ar";        document.body.style.direction = "rtl";
        if (logoImg) {
            logoImg.src = "../Auth/logo_ar.png";
            logoImg.onerror = () => { logoImg.src = "../Auth/logo.png"; };
        }

        document.getElementById("logoTextSpan").innerText = "نظام إدارة بيانات الخريجين | كلية الحاسب - جامعة القصيم";
        document.getElementById("navAbout").innerText = "عن النظام";
        document.getElementById("navFeatures").innerText = "الميزات";
        document.getElementById("navStart").innerText = "ابدأ الآن";

        document.getElementById("heroTitle").innerText = "نظام إدارة بيانات الخريجين";
        document.getElementById("heroText").innerText = "ربط كلية الحاسب بخريجيها من أجل مستقبل أقوى.";

        document.getElementById("btnLogin").innerText = "تسجيل دخول الخريجين";
        document.getElementById("btnSignup").innerText = "إنشاء حساب خريج";
        document.getElementById("btnAdmin").innerText = "تسجيل دخول المسؤول";

        document.getElementById("aboutTitle").innerText = "عن النظام";
        document.getElementById("aboutText").innerText =
            "يهدف نظام إدارة بيانات الخريجين إلى تعزيز التواصل بين كلية الحاسب وخريجيها، وتتبع مساراتهم المهنية، ومشاركة الإعلانات، وتنظيم الفعاليات.";

        document.getElementById("featuresTitle").innerText = "الميزات الرئيسية";

        document.getElementById("f1Title").innerText = "ملفات الخريجين";
        document.getElementById("f1Text").innerText = "يمكن للخريجين تحديث بياناتهم الشخصية والمهنية.";


        document.getElementById("f3Title").innerText = "فرص العمل";
        document.getElementById("f3Text").innerText = "عرض ومشاركة الوظائف لدعم التطور المهني.";

        document.getElementById("f4Title").innerText = "الفعاليات والإعلانات";
        document.getElementById("f4Text").innerText = "البقاء على اطلاع بآخر الأخبار والفعاليات.";

        document.getElementById("f5Title").innerText = "البحث والتصفية";
        document.getElementById("f5Text").innerText = "البحث عن الخريجين حسب التخصص أو سنة التخرج أو جهة العمل.";

        document.getElementById("f6Title").innerText = "وصول آمن";
        document.getElementById("f6Text").innerText = "تسجيل دخول آمن للخريجين والمسؤولين.";

        document.getElementById("startTitle").innerText = "ابدأ الآن";
        document.getElementById("startText").innerText = "اختر نوع الدخول:";

        document.getElementById("startLogin").innerText = "تسجيل دخول الخريجين";
        document.getElementById("startSignup").innerText = "إنشاء حساب خريج";
        document.getElementById("startAdmin").innerText = "تسجيل دخول المسؤول";

        document.getElementById("footerText").innerText =
            "© 2026 جامعة القصيم - كلية الحاسب | نظام إدارة بيانات الخريجين";

    } else {
        document.documentElement.lang = "en";
        document.body.style.direction = "ltr";
        if (logoImg) logoImg.src = "../Auth/logo.png";

        document.getElementById("logoTextSpan").innerText = "ADMS | COC - Qassim University";
        document.getElementById("navAbout").innerText = "About";
        document.getElementById("navFeatures").innerText = "Features";
        document.getElementById("navStart").innerText = "Get Started";

        document.getElementById("heroTitle").innerText = "Alumni Data Management System";
        document.getElementById("heroText").innerText = "Connecting the College of Computer with its graduates for a stronger future.";

        document.getElementById("btnLogin").innerText = "Alumni Login";
        document.getElementById("btnSignup").innerText = "Alumni Sign Up";
        document.getElementById("btnAdmin").innerText = "Admin Login";

        document.getElementById("aboutTitle").innerText = "About the System";
        document.getElementById("aboutText").innerText =
            "The Alumni Data Management System (ADMS) is designed for the College of Computer at Qassim University to maintain strong communication with graduates. It helps track alumni career paths, share announcements, manage events, and support long-term engagement.";

        document.getElementById("featuresTitle").innerText = "Main Features";

        document.getElementById("f1Title").innerText = "Alumni Profiles";
        document.getElementById("f1Text").innerText = "Graduates can update their personal and career information anytime.";

        document.getElementById("f3Title").innerText = "Job Opportunities";
        document.getElementById("f3Text").innerText = "Alumni can view and share job postings to support career growth.";

        document.getElementById("f4Title").innerText = "Events & Announcements";
        document.getElementById("f4Text").innerText = "Stay updated with college events, workshops, and important news.";

        document.getElementById("f5Title").innerText = "Search & Filtering";
        document.getElementById("f5Text").innerText = "Admins can search alumni by major, graduation year, or employer.";

        document.getElementById("f6Title").innerText = "Secure Access";
        document.getElementById("f6Text").innerText = "Role-based login ensures data safety for alumni and administrators.";

        document.getElementById("startTitle").innerText = "Get Started";
        document.getElementById("startText").innerText = "Select your role to continue:";

        document.getElementById("startLogin").innerText = "Alumni Login";
        document.getElementById("startSignup").innerText = "Alumni Sign Up";
        document.getElementById("startAdmin").innerText = "Admin Login";

        document.getElementById("footerText").innerText =
            "© 2026 Qassim University - College of Computer | Alumni Data Management System";
    }
}