<template>
  <div id="notFoundPage">
		<div class="notfound">
			<div class="notfound-404">
				<h1>Oops!</h1>
			</div>
			<h2>404 - Page not found</h2>
			<p>The page you are looking for might have been removed had its name changed or is temporarily unavailable.</p>
			<router-link :to="{ name: 'dashboard-page' }">Go To Homepage</router-link>
		</div>
	</div>
</template>
<script>
export default {
    name : 'notFoundPage',
    data(){
        return {
        token: false,
        }
    },
    mounted() {
        const isAuthenticated = localStorage.getItem('token');
        if(isAuthenticated){
            this.token = true;
        }else{
            this.token = false;
        }
    }, 
}
</script>