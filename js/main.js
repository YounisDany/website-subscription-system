// ملف JavaScript الرئيسي لنظام إدارة الاشتراكات

// تهيئة النظام عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    initializeSystem();
});

// دالة تهيئة النظام
function initializeSystem() {
    // تهيئة التأثيرات البصرية
    initializeAnimations();
    
    // تهيئة النماذج
    initializeForms();
    
    // تهيئة التنبيهات
    initializeAlerts();
    
    // تهيئة العدادات
    initializeCounters();
    
    // تهيئة الأحداث
    initializeEvents();
}

// تهيئة التأثيرات البصرية
function initializeAnimations() {
    // تأثير الظهور التدريجي للعناصر
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
            }
        });
    }, observerOptions);
    
    // مراقبة البطاقات والعناصر
    document.querySelectorAll('.card, .stats-card').forEach(el => {
        observer.observe(el);
    });
    
    // تأثير التحميل للأزرار
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.classList.contains('loading')) {
                this.classList.add('loading');
                setTimeout(() => {
                    this.classList.remove('loading');
                }, 2000);
            }
        });
    });
}

// تهيئة النماذج
function initializeForms() {
    // التحقق من صحة النماذج في الوقت الفعلي
    document.querySelectorAll('input[type="email"]').forEach(input => {
        input.addEventListener('blur', validateEmail);
        input.addEventListener('input', clearValidationError);
    });
    
    document.querySelectorAll('input[type="password"]').forEach(input => {
        input.addEventListener('input', function() {
            validatePasswordStrength(this);
            clearValidationError.call(this);
        });
    });
    
    document.querySelectorAll('input[required]').forEach(input => {
        input.addEventListener('blur', validateRequired);
        input.addEventListener('input', clearValidationError);
    });
    
    // تحسين تجربة المستخدم للنماذج
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}

// التحقق من صحة البريد الإلكتروني
function validateEmail() {
    const email = this.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        showFieldError(this, 'البريد الإلكتروني غير صحيح');
        return false;
    }
    
    clearFieldError(this);
    return true;
}

// التحقق من الحقول المطلوبة
function validateRequired() {
    if (!this.value.trim()) {
        showFieldError(this, 'هذا الحقل مطلوب');
        return false;
    }
    
    clearFieldError(this);
    return true;
}

// التحقق من قوة كلمة المرور
function validatePasswordStrength(input) {
    const password = input.value;
    const strengthIndicator = input.parentElement.querySelector('.password-strength');
    
    if (!strengthIndicator) return;
    
    let strength = 0;
    let feedback = '';
    
    if (password.length >= 8) strength += 25;
    if (/[a-z]/.test(password)) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 25;
    
    if (strength <= 25) {
        feedback = 'ضعيفة';
        strengthIndicator.className = 'password-strength weak';
    } else if (strength <= 50) {
        feedback = 'متوسطة';
        strengthIndicator.className = 'password-strength medium';
    } else if (strength <= 75) {
        feedback = 'جيدة';
        strengthIndicator.className = 'password-strength good';
    } else {
        feedback = 'قوية';
        strengthIndicator.className = 'password-strength strong';
    }
    
    strengthIndicator.textContent = feedback;
    strengthIndicator.style.width = strength + '%';
}

// عرض خطأ في الحقل
function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    field.parentElement.appendChild(errorDiv);
}

// إزالة خطأ من الحقل
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    
    const errorDiv = field.parentElement.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// إزالة خطأ التحقق
function clearValidationError() {
    clearFieldError(this);
}

// تهيئة التنبيهات
function initializeAlerts() {
    // إزالة التنبيهات تلقائياً
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // تأثيرات التنبيهات
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.animation = 'slideInDown 0.5s ease-out';
    });
}

// تهيئة العدادات
function initializeCounters() {
    // عداد الأرقام المتحركة
    document.querySelectorAll('.counter').forEach(counter => {
        const target = parseInt(counter.textContent);
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            counter.textContent = Math.floor(current);
        }, 16);
    });
}

// تهيئة الأحداث
function initializeEvents() {
    // تأثير النقر على البطاقات
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // تأثير النقر على الأزرار
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mousedown', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        btn.addEventListener('mouseup', function() {
            this.style.transform = 'scale(1)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // تحسين تجربة المستخدم للجداول
    document.querySelectorAll('.table tbody tr').forEach(row => {
        row.addEventListener('click', function() {
            // إزالة التحديد من الصفوف الأخرى
            document.querySelectorAll('.table tbody tr').forEach(r => {
                r.classList.remove('table-active');
            });
            
            // تحديد الصف الحالي
            this.classList.add('table-active');
        });
    });
}

// دالة عرض رسالة تأكيد مخصصة
function showConfirmDialog(title, message, onConfirm, onCancel) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirmBtn">تأكيد</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.querySelector('#confirmBtn').addEventListener('click', function() {
        if (onConfirm) onConfirm();
        bsModal.hide();
    });
    
    modal.addEventListener('hidden.bs.modal', function() {
        if (onCancel) onCancel();
        document.body.removeChild(modal);
    });
}

// دالة عرض رسالة نجاح
function showSuccessMessage(message) {
    showToast('success', message);
}

// دالة عرض رسالة خطأ
function showErrorMessage(message) {
    showToast('error', message);
}

// دالة عرض التنبيهات المنبثقة
function showToast(type, message) {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toastContainer.removeChild(toast);
    });
}

// إنشاء حاوي التنبيهات المنبثقة
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// دالة تحديث العداد التنازلي
function updateCountdown(endTime) {
    const now = new Date().getTime();
    const distance = endTime - now;
    
    if (distance < 0) {
        return null;
    }
    
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    return { days, hours, minutes, seconds };
}

// دالة تنسيق الأرقام
function formatNumber(num) {
    return num.toString().padStart(2, '0');
}

// دالة التحقق من الاتصال بالإنترنت
function checkConnection() {
    return navigator.onLine;
}

// مراقبة حالة الاتصال
window.addEventListener('online', function() {
    showSuccessMessage('تم استعادة الاتصال بالإنترنت');
});

window.addEventListener('offline', function() {
    showErrorMessage('انقطع الاتصال بالإنترنت');
});

// دالة حفظ البيانات محلياً
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
        return true;
    } catch (e) {
        console.error('خطأ في حفظ البيانات:', e);
        return false;
    }
}

// دالة استرجاع البيانات المحفوظة محلياً
function getFromLocalStorage(key) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch (e) {
        console.error('خطأ في استرجاع البيانات:', e);
        return null;
    }
}

// دالة تصدير البيانات
function exportData(data, filename) {
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    
    URL.revokeObjectURL(url);
}

// دالة طباعة الصفحة
function printPage() {
    window.print();
}

// دالة مشاركة الرابط
function shareLink(url, title) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // نسخ الرابط إلى الحافظة
        navigator.clipboard.writeText(url).then(() => {
            showSuccessMessage('تم نسخ الرابط إلى الحافظة');
        });
    }
}

// إضافة تأثيرات CSS ديناميكية
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInDown {
        from {
            transform: translate3d(0, -100%, 0);
            visibility: visible;
        }
        to {
            transform: translate3d(0, 0, 0);
        }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out;
    }
    
    .focused {
        transform: scale(1.02);
        transition: transform 0.2s ease;
    }
    
    .password-strength {
        height: 4px;
        border-radius: 2px;
        transition: all 0.3s ease;
        margin-top: 5px;
    }
    
    .password-strength.weak { background-color: #dc3545; }
    .password-strength.medium { background-color: #ffc107; }
    .password-strength.good { background-color: #17a2b8; }
    .password-strength.strong { background-color: #28a745; }
    
    .table tbody tr {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.1);
        transform: scale(1.01);
    }
    
    .table tbody tr.table-active {
        background-color: rgba(0, 123, 255, 0.2);
    }
`;

document.head.appendChild(style);

