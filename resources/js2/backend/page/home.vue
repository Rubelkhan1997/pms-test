<template>
    <div class="">
        <span v-if="$route.name == 'profile-page'"> 
            Profle Page
        </span>
        <span v-else>
            Setting Page
        </span>

    </div>
</template>
<script>
export default {
    name: 'home',
}
</script>