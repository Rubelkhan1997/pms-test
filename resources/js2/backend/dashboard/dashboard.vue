<template>
    <div id="dashboard-page">
        dashboard
    </div>
</template>

<script>
export default {
  name: 'dashboard-page',
  data(){
    return {
      token: true,
      asset: "",
    }
  },
  mounted() {
    EventBus.$emit('newtaks', this.token);
    this.asset = Base_Url;
  }, 
  // methods: {
      
  // },
}
</script>