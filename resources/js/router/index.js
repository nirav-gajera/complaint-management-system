import { createRouter, createWebHistory } from 'vue-router';
import { pinia } from '../stores/pinia';
import { useAuthStore } from '../stores/auth';
import LoginPage from '../pages/auth/LoginPage.vue';
import RegisterPage from '../pages/auth/RegisterPage.vue';
import ForgotPasswordPage from '../pages/auth/ForgotPasswordPage.vue';
import ResetPasswordPage from '../pages/auth/ResetPasswordPage.vue';
import VerifyEmailPage from '../pages/auth/VerifyEmailPage.vue';
import UserDashboardPage from '../pages/user/UserDashboardPage.vue';
import ComplaintsPage from '../pages/user/ComplaintsPage.vue';
import ComplaintCreatePage from '../pages/user/ComplaintCreatePage.vue';
import ComplaintShowPage from '../pages/user/ComplaintShowPage.vue';
import ProfilePage from '../pages/user/ProfilePage.vue';
import AdminDashboardPage from '../pages/admin/AdminDashboardPage.vue';
import AdminComplaintsPage from '../pages/admin/AdminComplaintsPage.vue';
import AdminComplaintShowPage from '../pages/admin/AdminComplaintShowPage.vue';
import AdminCategoriesPage from '../pages/admin/AdminCategoriesPage.vue';
import AdminSettingsPage from '../pages/admin/AdminSettingsPage.vue';
import AdminUsersPage from '../pages/admin/AdminUsersPage.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: '/dashboard' },
        { path: '/login', component: LoginPage, meta: { guest: true } },
        { path: '/register', component: RegisterPage, meta: { guest: true } },
        { path: '/forgot-password', component: ForgotPasswordPage, meta: { guest: true } },
        { path: '/reset-password/:token?', component: ResetPasswordPage, meta: { guest: true } },
        { path: '/verify-email', component: VerifyEmailPage, meta: { requiresAuth: true } },
        { path: '/dashboard', component: UserDashboardPage, meta: { requiresAuth: true, requiresVerified: true, section: 'user' } },
        { path: '/complaints', component: ComplaintsPage, meta: { requiresAuth: true, requiresVerified: true, section: 'user' } },
        { path: '/complaints/new', component: ComplaintCreatePage, meta: { requiresAuth: true, requiresVerified: true, section: 'user' } },
        { path: '/complaints/:id', component: ComplaintShowPage, meta: { requiresAuth: true, requiresVerified: true, section: 'user' } },
        { path: '/profile', component: ProfilePage, meta: { requiresAuth: true, section: 'user' } },
        { path: '/admin/dashboard', component: AdminDashboardPage, meta: { requiresAuth: true, requiresVerified: true, requiresStaff: true, section: 'admin' } },
        { path: '/admin/complaints', component: AdminComplaintsPage, meta: { requiresAuth: true, requiresVerified: true, requiresStaff: true, section: 'admin' } },
        { path: '/admin/complaints/:id', component: AdminComplaintShowPage, meta: { requiresAuth: true, requiresVerified: true, requiresStaff: true, section: 'admin' } },
        { path: '/admin/categories', component: AdminCategoriesPage, meta: { requiresAuth: true, requiresVerified: true, requiresStaff: true, section: 'admin' } },
        { path: '/admin/settings', component: AdminSettingsPage, meta: { requiresAuth: true, requiresVerified: true, requiresStaff: true, section: 'admin' } },
        { path: '/admin/users', component: AdminUsersPage, meta: { requiresAuth: true, requiresVerified: true, requiresStaff: true, section: 'admin' } },
    ],
});

router.beforeEach(async (to) => {
    const auth = useAuthStore(pinia);
    await auth.initialize();

    if (to.meta.guest && auth.isAuthenticated) {
        return auth.isStaff ? '/admin/dashboard' : '/dashboard';
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return '/login';
    }

    if (to.meta.requiresStaff && !auth.isStaff) {
        return '/dashboard';
    }

    if (to.meta.requiresVerified && auth.user && !auth.user.email_verified_at) {
        return '/verify-email';
    }

    return true;
});

export default router;
