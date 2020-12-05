<template>
  <div id="home" class="container">
    <div class="row justify-content-center">
      <div class="col-12">
        <div id="users" v-if="user.role == 'admin'" class="card">
          <div class="card-header">Users</div>

          <div class="card-body">
            <div v-if="selectedUser !== null" class="row position-relative">
              <div class="col-12">
                <h5>User: {{selectedUser.name}}</h5>
                <h5 class="mt-3">Onboarding Tasks Progression</h5>
                <div class="row mt-3">
                  <div v-for="(task, key) in selectedUser.tasks" class="col">
                    <div class="form-check">
                      <input @click="updateTask(selectedUser, key, $event)" type="checkbox" :model="parseInt(task)" :checked="parseInt(task)" class="form-check-input">
                      <label :for="key" class="form-check-label">{{titleCase(key.replace(/_/g, " ", ))}}</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="users.length" class="row mt-3">
              <div v-for="user in users" :key="user.id" class="col-12 col-md-4 mt-4">
                <div class="col-12 user-wrapper py-3">
                  <p>Name: {{user.name}}</p>
                  <p>Id: {{user.id}}</p>
                  <p>Email: {{user.email}}</p>
                  <button @click="selectUser(user)" type="button" class="btn btn-secondary mt-2">Check user</button>
                </div>
              </div>
            </div>
            <div v-else class="row my-3">
              <div class="col-12">
                <h5>You don't have any employees added yet</h5>
                <p class="mt-3">To add an employee, click on the button below or navigate in your menu to Administration->Add employee</p>
                <button @click="$router.push('employee/add')" type="button" class="btn btn-primary mt-2">Add employee</button>
              </div>
            </div>

            <div v-if="loading" class="loader-wrapper">
              <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
            </div>
          </div>
        </div>
        <div id="requests" v-else class="card">
          <div class="card-header">Requests</div>

          <div class="card-body">
            <div class="row">
              <template v-if="requests.length" class="col-12">
                <div v-for="(request) in requests" :key="request.id" class="col-12 col-md-4 py-2">
                  <div class="py-2 px-2 request-wrapper">
                    <h5>Date: {{request.date}}</h5>
                    <p><b>Hours:</b> {{request.hours}}</p>
                    <p>
                      <b>Status:</b>
                      <span v-if="request.status == 0">Not yet approved</span>
                      <span v-else-if="request.status == 1">Approved</span>
                      <span v-else-if="request.status == 2">Denied</span>
                    </p>
                    <button :disabled="request.status != 0" @click="deleteRequest(request)" type="submit" class="mt-3 btn btn-danger">Delete</button>
                  </div>
                </div>
              </template>
              <div v-else class="col-12 my-3">
                <h5>You have no active requests</h5>
                <p class="mt-3">To add a request, click on the button below or navigate in your menu to Requests->Add</p>
                <button @click="$router.push('request/add')" type="button" class="btn btn-primary mt-2">Add request</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        loading: false,
        users: [],
        selectedUser: null,
        requests: [],
      }
    },
    computed: {
      user() {
        return this.$store.state.user;
      }
    },
    mounted() {
      if (user.role == 'admin') {
        axios.get('/users/all')
        .then((response) => {
          // Filter the repsonse array that it only return users that are not admins
          this.users = response.data.filter(user => user.role_id != 1);
        });
      } else {
        axios.get('/request/logged_in_users_requests')
        .then((response) => {
          if (response.data.success) {
            this.requests = response.data.requests;
          }
        });
      }
    },
    methods: {
      selectUser(user) {
        this.loading = true;

        if (this.selectedUser != user) {
          axios.get(`/user/get_by_id?id=${user.id}`)
          .then((response) => {
            this.selectedUser = response.data;
          }).finally(() => {
            setTimeout(() => {
              this.loading = false;
            }, 400);
          });
        }
      },
      updateTask(user, task, e) {
        const data = {
          task: task,
          user_id: user.id,
          value: e.target.checked
        };

        axios.post('/user/update_task', data)
        .then((response) => {
          this.$toastr.s('Task updated');
        });
      },
      deleteRequest(request) {
        // Employee cannot delete processed requests
        if (request.status != 0) {
          this.$toastr.e('This request can be deleted as it has already been processed');
          return;
        }


        axios.post('/request/delete_by_id', {id: request.id})
        .then((response) => {
          if (response.data.success) {
            this.$toastr.s('Request deleted');
            this.requests.splice(this.requests.indexOf(request), 1);
          } else {
            this.$toastr.e('Couldn\'t delete request');
          }
        });
      },
      titleCase(str) {
         var splitStr = str.toLowerCase().split(' ');
         for (var i = 0; i < splitStr.length; i++) {
             splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
         }
         // Directly return the joined string
         return splitStr.join(' ');
      }
    }
  }
</script>
