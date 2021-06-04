<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }}
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
            <el-table-column align="center" :label="$t('sms.user_name')" width="180">
        <template slot-scope="scope">
          <span>{{ scope.row.user_name }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" :label="$t('sms.phone_number')" width="280">
        <template slot-scope="scope">
          <span>{{ scope.row.phone_number }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" :label="$t('sms.server_name')" >
        <template slot-scope="scope">
          <span>{{ scope.row.server_name}}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="350">
        <template slot-scope="scope">

          <el-button v-role="['admin']"  type="primary" size="small" icon="el-icon-edit" @click="handleUpdate(scope.row.phone_number,scope.row.user_id)">
            Edit
          </el-button>

          <!-- <el-button v-if="!scope.row.roles.includes('admin')" v-permission="['manage permission']" type="warning" size="small" icon="el-icon-edit" @click="handleEditPermissions(scope.row.id);">
            Permissions
          </el-button> -->
          <el-button v-role="['admin']"  type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.phone_number,scope.row.user_id);">
            Delete
          </el-button>
        </template>
      </el-table-column>
    </el-table>


    <el-dialog v-loading="loadingAdd" :title="'Create new connection'" :visible.sync="dialogFormVisible">
      <div v-loading="userCreating" class="form-container">
        <el-form ref="newSms" :rules="rules" :model="newSms" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('sms.server_name')" prop="server_id">
               <el-select multiple filterable  
                class="filter-item"
                collapse-tags
                v-model="newSms.server_id"
                placeholder="Please select servers" required>
                <el-option v-for="item in servers"
                        class="filter-item"
                        :value="item.id"
                        :label="item.name | uppercaseFirst"
                        :key="item.id">
                </el-option>
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('sms.user_name')" prop="user_id">
             <el-select v-model="newSms.user_id" class="filter-item" placeholder="Please select user" filterable >
              <el-option v-for="item in userList" :key="item.id" :label="item.name | uppercaseFirst" :value="item.id" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('sms.phone_number')" prop="phone_number">
            <el-input v-model="newSms.phone_number" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="createUser()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
    <el-dialog v-loading="editLoading" :visible.sync="dialogFormUpdateVisible">
      <el-form ref="dataForm" label-position="left" label-width="70px" style="width: 400px; margin-left:50px;">
        <el-form-item :label="$t('sms.user_name')" prop="user_id">
              <el-input v-model="formEditSms.editUserName" :disabled="true" />
          </el-form-item>
           <el-form-item :label="$t('sms.phone_number')" prop="phone_number">
            <el-input v-model="formEditSms.editPhoneNumber" :disabled="true"/>
          </el-form-item>
        <el-form-item :label="$t('sms.server_name')" prop="server_id">
           <el-select multiple filterable  
                class="filter-item"
                collapse-tags
                v-model="formEditSms.editServerId"
                placeholder="Please select servers" required>
                <el-option v-for="item in servers"
                        class="filter-item"
                        :value="item.id"
                        :label="item.name | uppercaseFirst"
                        :key="item.id">
                </el-option>
            </el-select>
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
    </el-dialog>
  </div>
</template>

<script>
import SmsResource from '@/api/sms';
import { getServerList, getUserList, editList,UpdateSmsConnection,DeleteSms} from '@/api/sms';
import waves from '@/directive/waves'; // Waves directive
import role from '@/directive/role/index.js';
const smsResource = new SmsResource();

export default {
  name: 'UserList',
  directives: { role,waves },
  data() {
    return {
      newSms: {
          user_id: '',
          server_id:[],
          phone_number: '',
      },
      servers: [],
      userList: [],
      formEditSms: {
        editPhoneNumber: '',
        editServerId: [],
        editUserId: '',
        editUserName:'',
      },
      editList: {
        sendPhone: '',
        sendUser: '',
      },
      getEdit: {
        phone_number: '',
        user_id: '',
      },
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
      rules: {
        server_id: [{ required: true, message: 'Server is required', trigger: 'change' }],
        user_id: [{ required: true, message: 'User is required', trigger: 'blur' }],
        phone_number: [{ required: true, message: 'Phone number is required', trigger: 'blur' }],
      },
    };
  },
  created() {
    this.resetNewUser();
    this.getListSms();
  },
  methods: {
      async getListSms() {
      this.loading = true;
      await smsResource.list(this.query).then(response => {
       
        this.list = response.data.result
      });
      this.loading = false;
      },

    async handleUpdate(phone_number,user_id) {
      this.editLoading = true;
      this.editList.sendPhone = phone_number;
      this.editList.sendUser = user_id;
      this.getEdit.phone_number = phone_number;
      this.getEdit.user_id = user_id;
      await getServerList().then(response => {
          this.servers = response.data.items;
      });
      await getUserList().then(response => {
          this.userList = response.data.userList;
      });
      await editList(user_id,phone_number).then(response => {
        console.log(response);
         response.data.result.forEach((element) => {
         this.formEditSms.editServerId = element['server_id'];
         this.formEditSms.editUserName = element['user_name']; 
         this.formEditSms.editPhoneNumber = element['phone_number'];
         this.formEditSms.editUserId = element['user_id'];
      });
      });
      this.dialogFormUpdateVisible = true;
      this.editLoading = false;
    },
      async update(){
      await UpdateSmsConnection(this.formEditSms).then((response) => {
        console.log(response);
        this.dialogFormUpdateVisible = false;
        this.$notify({
          title: 'Success',
          message: 'Updated successfully',
          type: 'success',
          duration: 2000,
        });
      });

      this.getListSms();
    },
    handleFilter() {
      this.query.page = 1;
      this.getListSms();
    },
    async handleCreate() {
      this.loadingAdd = true;
      await getServerList().then(response => {
          this.servers = response.data.items;
      });
      await getUserList().then(response => {
          this.userList = response.data.userList;
      });
      this.resetNewUser();
      this.dialogFormVisible = true;

      this.loadingAdd = false;
    },
    handleDelete(phone_number, user_id) {
      this.$confirm('This will permanently delete connection ' + phone_number + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        DeleteSms(phone_number,user_id).then(response => {
          this.$message({
            type: 'success',
            message: 'Delete completed',
          });
          this.handleFilter();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled',
        });
      });
       this.getListSms();
    },

    createUser() {
      this.$refs['newSms'].validate((valid) => {
        if (valid) {
          this.userCreating = true;
          smsResource
            .store(this.newSms)
            .then(response => {
              console.log(response);
              this.$message({
                message: 'New sms connection has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewUser();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.userCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetNewUser() {
      this.newSms = {
          user_id: '',
          server_id:[],
          phone_number: '',
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'user_id', 'name', 'email', 'role'];
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
