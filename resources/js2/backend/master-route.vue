<template >
    <div id="master-page" class="wrapper">
      	<sidebarPage v-if="$route.path != '/' && token != false"></sidebarPage>
		<div class="main">
			<!--  Responsive Sidebar Start -->
			<headerPage v-if="$route.path != '/' && token != false"></headerPage>
			<!-- Responsive Sidebar End -->
			<!-- Content Page Start -->
			<main class="content">
				<div class="container-fluid p-0">
					<div class="row">
						<router-view></router-view>
					</div>
				</div>
			</main>
			<!-- Content Page End -->
			<footerPage v-if="$route.path != '/' && token != false"></footerPage>
			<!-- {{ $route.name }}
			{{ $route.path }} -->
		</div>
	</div>
</template>

<script>
import headerPage from "./dashboard/header";
import sidebarPage from "./dashboard/sidebar";
import footerPage from "./dashboard/footer";

export default {
    name: 'master-page',
	data(){
      return {
        token: false,
      }
    },
	components: {
        headerPage,
        sidebarPage,
        footerPage,
    },
	mounted() {
		const isAuthenticated = localStorage.getItem('token');
		if(isAuthenticated){
			this.token = true;
		}else{
			this.token = false;
		}
	},
	created() {
		EventBus.$on('newtaks', (tokenCatch)=>{
			this.token = tokenCatch;
		});
    }, 
	methods:{
	
	},
}
</script>