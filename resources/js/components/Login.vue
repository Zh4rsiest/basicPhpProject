<template>
  <div class="container">
    <div class="row d-flex justify-content-center">
      <div class="col-6">
        <form id="loginForm" class="position-relative">
          <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="text" class="form-control" name="email" placeholder="username@domain.com">
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" class="form-control" name="password" placeholder="**********">
          </div>

          <button @click="login" type="submit" class="btn btn-primary">Login</button>

          <div v-if="loading" class="loader-wrapper">
            <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        loading: false,
      }
    },
    methods: {
      login(e) {
        e.preventDefault();
        var formData = new FormData(document.getElementById("loginForm"));
        this.loading = true;

        axios.post('login', formData)
        .then((response) => {
          if (response.data.success) {
            this.$store.dispatch('fetchUser');
            window.location.href = "/";
          } else {
            this.$toastr.e('Couldn\'t log in');
          }
        }).finally(() => {
          this.loading = false;
        });
      }
    }
  }
</script>
