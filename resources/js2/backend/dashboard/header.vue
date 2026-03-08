<template>
    <!--  Header Part Start -->
    <nav id="header-part" class="navbar navbar-expand navbar-light navbar-bg">
        <a class="sidebar-toggle">
            <i class="hamburger align-self-center"></i>
        </a>

        <form class="d-none d-sm-inline-block">
            <div class="input-group input-group-navbar">
                <input type="text" class="form-control" placeholder="Search projects…" aria-label="Search">
                <button class="btn" type="button">
                    <i class="align-middle" data-feather="search"></i>
                </button>
            </div>
        </form>

        <div class="navbar-collapse collapse">
            <ul class="navbar-nav navbar-align">
                <li class="nav-item dropdown">
                    <a class="nav-icon dropdown-toggle" href="#" id="messagesDropdown" data-bs-toggle="dropdown">
                        <div class="position-relative">
                            <i class="align-middle" data-feather="message-circle"></i>
                            <span class="indicator">4</span>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="messagesDropdown">
                        <div class="dropdown-menu-header">
                            <div class="position-relative">
                                4 New Messages
                            </div>
                        </div>
                        <div class="list-group">
                            <a href="#" class="list-group-item">
                                <div class="row g-0 align-items-center">
                                    <div class="col-2">
                                        <img :src="'backend/img/avatars/avatar-5.jpg'" class="avatar img-fluid rounded-circle" alt="Ashley Briggs">
                                    </div>
                                    <div class="col-10 ps-2">
                                        <div class="text-dark">Ashley Briggs</div>
                                        <div class="text-muted small mt-1">Nam pretium turpis et arcu. Duis arcu tortor.</div>
                                        <div class="text-muted small mt-1">15m ago</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="dropdown-menu-footer">
                            <a href="#" class="text-muted">Show all messages</a>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
                        <div class="position-relative">
                            <i class="align-middle" data-feather="bell-off"></i>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="alertsDropdown">
                        <div class="dropdown-menu-header">
                            4 New Notifications
                        </div>
                        <div class="list-group">
                            <a href="#" class="list-group-item">
                                <div class="row g-0 align-items-center">
                                    <div class="col-2">
                                        <i class="text-danger" data-feather="alert-circle"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="text-dark">Update completed</div>
                                        <div class="text-muted small mt-1">Restart server 12 to complete the update.</div>
                                        <div class="text-muted small mt-1">2h ago</div>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item">
                                <div class="row g-0 align-items-center">
                                    <div class="col-2">
                                        <i class="text-success" data-feather="user-plus"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="text-dark">New connection</div>
                                        <div class="text-muted small mt-1">Anna accepted your request.</div>
                                        <div class="text-muted small mt-1">12h ago</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="dropdown-menu-footer">
                            <a href="#" class="text-muted">Show all notifications</a>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-flag dropdown-toggle" href="#" id="languageDropdown" data-bs-toggle="dropdown">
                        <img src="http://127.0.0.1:8000/backend/img/flags/us.png" alt="English" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                        <a class="dropdown-item" href="#">
                            <img :src="`${asset}/backend/img/flags/us.png`" alt="English" width="20" class="align-middle me-1" />
                            <span class="align-middle">English</span>
                        </a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                        <i class="align-middle" data-feather="settings"></i>
                    </a>

                    <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                        <img :src="`${asset}/backend/img/avatars/rubel.jpg`" class="avatar img-fluid rounded-circle me-1" alt="Chris Wood" /> <span class="text-dark">{{ auth.name }} </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <router-link :to="{ name: 'profile-page' }" class="dropdown-item" ><i class="align-middle me-1" data-feather="user"></i> Profile</router-link>
                        <div class="dropdown-divider"></div>
                        <router-link :to="{ name: 'setting-page' }" class="dropdown-item">Settings</router-link>
                        <a @click.prevent="logout()" href="javascript:void(0)" class="dropdown-item">Log out</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    <!--  Header Part End -->
</template>

<script>
export default {
    name: "header-part",
    data(){
      return {
        asset: "",
        auth: "",
      }
    },
    created(){
        this.asset = Base_Url;
        this.auth  = JSON.parse(localStorage.getItem('auth'));
        // console.log(JSON.parse(Auth).name);
    },
    methods:{
        logout(){
            axios.get(Api_Base_Url+'/logout').then(res =>{
                // Message
                this.$message({
                    showClose: true,
                    message: res.data.success,
                    type: 'success',
                });
                // Local Storage Information Remove
                localStorage.removeItem('token'); 
                localStorage.removeItem('auth'); 
                // Home Page Redirect 
                this.$router.push({ name: 'login-page' });
            })
        },

    },
}
</script>