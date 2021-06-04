<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <!-- <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus">
        {{ $t('table.add') }}
      </el-button> -->
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Name">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Description">
        <template slot-scope="scope">
          <span>{{ scope.row.description }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Created date">
        <template slot-scope="scope">
          <span>{{ format_date(scope.row.created_at) }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Action" width="350">
        <template slot-scope="scope">
          <!-- <el-button v-role="['admin']" type="primary" size="small" icon="el-icon-edit" @click="handleUpdate(scope.row.id)">
            Edit
          </el-button> -->

          <el-button v-role="['admin']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);">
            Delete
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <!-- <el-dialog v-loading="editLoading" :visible.sync="dialogFormUpdateVisible">
      <el-form ref="dataForm" label-position="left" label-width="70px" style="width: 400px; margin-left:50px;">
        <el-form-item :label="$t('user.name')" prop="title">
          <el-input v-model="formupdate.editname" value="formupdate.editname" />
        </el-form-item>
        <el-form-item :label="$t('user.email')" prop="title">
          <el-input v-model="formupdate.editdescription" value="formupdate.editdescription" />
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormUpdateVisible = false">
          {{ $t('table.cancel') }}
        </el-button>
        <el-button type="primary" @click="update">
          {{ $t('table.confirm') }}
        </el-button>
      </div>
    </el-dialog> -->
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import ServerResource from '@/api/type';
import Resource from '@/api/resource';
import moment from 'moment';
import waves from '@/directive/waves'; // Waves directive
import role from '@/directive/role/index.js';
const serverResource = new ServerResource();
const permissionResource = new Resource('permissions');

export default {
  name: 'ServerGroupsList',
  components: { Pagination },
  directives: { waves, role },
  data() {
    return {
      formupdate: {
        editname: '',
        editdescription: '',
        editid: '',
      },
      list: null,
      total: 0,
      loading: true,
      editLoading: false,
      downloading: false,
      userCreating: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      loadingAdd: false,
      list: [],
      dialogFormVisible: false,
      dialogPermissionVisible: false,
      dialogPermissionLoading: false,
      dialogFormUpdateVisible: false,
    };
  },
  created() {
    this.resetNewUser();
    this.getList();
  },
  methods: {
    format_date(value){
      if (value) {
        return moment(String(value)).format('YYYY-MM-DD h:mm:ss');
      }
    },
    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await serverResource.list(this.query);
      this.list = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
    async update() {
      this.editLoading = true;
      await serverResource.update(this.formupdate.editid, this.formupdate)
        .then(response => {
          this.$message({
            message: 'Server group ' + this.formupdate.editname + ' has been changed successfully.',
            type: 'success',
            duration: 5 * 1000,
          });
        })
        .catch(error => {
          console.log(error);
        })
        .finally(() => {
          this.dialogFormUpdateVisible = false;
          this.getList();
        });
      this.editLoading = false;
    },
    async handleUpdate(id) {
      this.editLoading = true;
      serverResource
        .get(id)
        .then(response => {
          this.formupdate.editname = response.data.group.name;
          this.formupdate.editdescription = response.data.group.description;
          this.formupdate.editid = response.data.group.id;
        })
        .catch(error => {
          console.log(error);
        })
        .finally(() => {
          this.editLoading = false;
        });
      this.dialogFormUpdateVisible = true;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleDelete(id, name) {
      this.$confirm('This will permanently delete server group ' + name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        serverResource.destroy(id).then(response => {
          this.$message({
            type: 'success',
            message: 'Delete completed',
          });
          this.getList();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled',
        });
      });
    },
    // createUser() {
    //   this.$refs['userForm'].validate((valid) => {
    //     if (valid) {
    //       this.newUser.roles = [this.newUser.role];

    //       this.userCreating = true;
    //       serverResource
    //         .store(this.newUser)
    //         .then(response => {
    //           console.log(this.newUser.roles);
    //           this.$message({
    //             message: 'New user ' + this.newUser.name + '(' + this.newUser.email + ') has been created successfully.',
    //             type: 'success',
    //             duration: 5 * 1000,
    //           });
    //           this.resetNewUser();
    //           this.dialogFormVisible = false;
    //           this.handleFilter();
    //         })
    //         .catch(error => {
    //           console.log(error);
    //         })
    //         .finally(() => {
    //           this.userCreating = false;
    //         });
    //     } else {
    //       console.log('error submit!!');
    //       return false;
    //     }
    //   });
    // },
    resetNewUser() {
      this.newUser = {
        name: '',
        email: '',
        password: '',
        confirmPassword: '',
        role: 'user',
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        // const tHeader = ['id', 'user_id', 'name', 'email', 'role'];
        const filterVal = ['index', 'id', 'name', 'email', 'role'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'user-list',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]));
    },

  },
};
</script>

<style lang="scss" scoped>
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}
.dialog-footer {
  text-align: left;
  padding-top: 0;
  margin-left: 150px;
}
.app-container {
  flex: 1;
  justify-content: space-between;
  font-size: 14px;
  padding-right: 8px;
  .block {
    float: left;
    min-width: 250px;
  }
  .clear-left {
    clear: left;
  }
}
</style>
