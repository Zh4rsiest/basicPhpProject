<template>
  <nav class="navbar navbar-expand-lg mb-3">
    <router-link class="navbar-brand" to="/">Blexr</router-link>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ml-auto">
        <template v-if="!user">
          <li class="nav-item">
            <router-link class="nav-link" :class="{active: currentRouteName == 'login'}" to="/login">Login</router-link>
          </li>
        </template>
        <template v-else>
          <li v-if="user.role === 'admin'" class="nav-item dropdown">
            <a :class="{active: currentRouteName == 'administration'}" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Administration</a>
            <div class="dropdown-menu dropdown-menu-right">
              <router-link :class="{active: currentRouteName == 'employee/add'}" class="dropdown-item" to="/employee/add">Add employee</router-link>
              <router-link :class="{active: currentRouteName == 'requests'}" class="dropdown-item" to="/requests">Pending requests</router-link>
            </div>
          </li>
          <li v-else class="nav-item dropdown">
            <a :class="{active: currentRouteName == 'Requests'}" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Requests</a>
            <div class="dropdown-menu dropdown-menu-right">
              <router-link :class="{active: currentRouteName == 'request/add'}" class="dropdown-item" to="/request/add">Add Request</router-link>
              <router-link :class="{active: currentRouteName == '/'}" class="dropdown-item" to="/">All</router-link>
            </div>
          </li>

          <li class="nav-item">
            <a @click="logout" class="nav-link">Logout</a>
          </li>
        </template>
      </ul>
    </div>

  </nav>
</template>

<script>
  export default {
    computed: {
      user() {
        return this.$store.state.user;
      },
      currentRouteName() {
        return this.$route.name;
      }
    },
    mounted() {

    },
    methods: {
      logout(e) {
        e.preventDefault();

        axios.post('/logout')
        .then((response) => {
          if (response.data.success) {
            window.location.href = "/";
          } else {
            console.log('Couldn\'t log out');
          }
        })
      },
    }
  }
</script>
