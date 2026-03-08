<template>
    <!-- Sidebar Start -->
    <nav id="sidebar-part" class="sidebar">
        <div class="sidebar-content js-simplebar">
            <router-link :to="{ name: 'dashboard-page' }" class="sidebar-brand">
                <span class="align-middle me-3">My Account</span>
            </router-link>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <router-link :to="{ name: 'dashboard-page' }" class="sidebar-link">
                        <i class="fas fa-tachometer-alt"></i> <span class="align-middle">Dashboard</span>
                    </router-link>
                </li>
                 <li class="sidebar-item">
                    <router-link :to="{ name: 'account-page' }" class="sidebar-link">
                        <i class="fas fa-puzzle-piece"></i><span> Account </span>
                    </router-link>
                </li>
                <li class="sidebar-item">
                    <a data-bs-target="#account-report" data-bs-toggle="collapse" class="sidebar-link collapsed" aria-expanded="false">
                        <i class="fas fa-user-friends"></i> <span class="align-middle">Account Report</span>
                    </a>
                    <ul id="account-report" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar-part">
                        <li :class="$route.name == 'account-cost-report' ?   'sidebar-item active' :'sidebar-item'"><router-link :to="{ name: 'account-cost-report' }" class="sidebar-link"><i class="fas fa-code"> </i>Cost</router-link></li>
                        <li :class="$route.name == 'account-credit-report' ? 'sidebar-item active' :'sidebar-item'"><router-link :to="{ name: 'account-credit-report' }" class="sidebar-link"><i class="fas fa-code"> </i> Credit</router-link></li>
                        <li :class="$route.name == 'account-debit-report' ?  'sidebar-item active' :'sidebar-item'"><router-link :to="{ name: 'account-debit-report' }" class="sidebar-link"><i class="fas fa-code"> </i> Debit</router-link></li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a data-bs-target="#bank-report" data-bs-toggle="collapse" class="sidebar-link collapsed" aria-expanded="false">
                        <i class="fas fa-user-friends"></i> <span class="align-middle">Bank</span>
                    </a>
                    <ul id="bank-report" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar-part">
                        <li :class="$route.name == 'bank-deposit-page'  ? 'sidebar-item active' :'sidebar-item'"><router-link :to="{ name: 'bank-deposit-page' }" class="sidebar-link"><i class="fas fa-code"> </i> Deposit</router-link></li>
                        <li :class="$route.name == 'bank-transfer-page' ? 'sidebar-item active' :'sidebar-item'"><router-link :to="{ name: 'bank-transfer-page' }" class="sidebar-link"><i class="fas fa-code"> </i> Transfer</router-link></li>
                        <li :class="$route.name == 'bank-report'        ? 'sidebar-item active' :'sidebar-item'"><router-link :to="{ name: 'bank-report' }" class="sidebar-link"><i class="fas fa-code"> </i> Report</router-link></li>
                    </ul>
                </li>



                <!-- <li class="sidebar-item">
                    <a class="sidebar-link" href="profile_list.html" >
                        <i class="fas fa-puzzle-piece"></i><span> Profile </span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="calendar.html">
                        <i class="fas fa-cog"></i> <span class="align-middle">Settings</span>
                    </a>
                </li> -->
            </ul>
        </div>
    </nav>
    <!-- sidebar End -->
</template>
<script>
export default {
    name:"sidebar-part",
}
</script>