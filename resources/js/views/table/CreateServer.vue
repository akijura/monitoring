<template>
  <div class="app-container">
    <el-button class="filter-item" style="margin-left: 120px; margin-bottom: 10px;" type="success" icon="el-icon-plus" @click="handleCreateTypeServer">
      {{ $t('table.addType') }}
    </el-button>
    <el-form ref="dataForm" :rules="rules" v-loading="typeLoading" :model="form" label-width="120px">

      <el-form-item label="Server name" prop="name">
        <el-input v-model="form.name" />
      </el-form-item>
      <el-form-item label="Server Type" prop="type">
        <el-select v-model="form.type" placeholder="please select server type" >
          <el-option label="Production" value="production" />
          <el-option label="Test" value="test" />
        </el-select>
      </el-form-item>
      <el-form-item label="Request Type" prop="request_type">
        <el-select v-model="form.request_type" :placeholder="$t('table.requestType')" clearable class="filter-item">
          <el-option v-for="item in request_types" :key="item" :label="item | uppercaseFirst" :value="item" />
        </el-select>
      </el-form-item>
      <el-form-item label="Server Host IP" prop="ip">
        <el-input v-model="form.ip" />
      </el-form-item>
      <el-form-item label="Server Port" prop="port">
        <el-input v-model="form.port" />
      </el-form-item>
      <el-form-item label="Login">
        <el-input v-model="form.login" />
      </el-form-item>
      <el-form-item label="Password">
        <el-input v-model="form.password" />
      </el-form-item>
      <el-form-item label="Server Endpoint">
        <el-input v-model="form.endpoint" />
      </el-form-item>
      <el-form-item label="Server Type 2" prop="shina">
        <el-select v-model="form.shina" placeholder="please select server type" >
          <el-option v-for="type in server_types" :key="type" :value="type" :label="type" />
        </el-select>
      <!-- <el-button class="cancel-btn" size="small" icon="el-icon-refresh" type="warning"  @click="handleRefresh">
              Refresh
            </el-button> -->
        <!-- <el-select v-model="form.shina" placeholder="please select server type 2">
          <el-option label="Shina" value="1" />
          <el-option label="Other" value="0" />
        </el-select> -->
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="onSubmit">
          Create
        </el-button>
        <el-button @click="onCancel">
          Cancel
        </el-button>
      </el-form-item>
    </el-form>
    <el-dialog v-loading="typeCreating" :title="'Create new server type'" :visible.sync="dialogFormVisible">
      <div class="form-container">
        <el-form ref="roleForm" :model="formType" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('table.name')" prop="type">
            <el-input v-model="formType.name" />
          </el-form-item>
          <el-form-item :label="$t('table.description')" prop="description">
            <el-input v-model="formType.desc" />
          </el-form-item>
        </el-form>

        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="onSubmitType">
            {{ $t('table.add') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { createServer, createServerType, getServerTypes } from '@/api/servers';

export default {
  //     created () {
  //     this.handleRefresh();
  //     },
  // mounted(){
  //   this.handleRefresh();
  // },

  data() {
    return {
      server_types: [],
      typeLoading: false,
      form: {
        name: '',
        type: '',
        ip: '',
        port: '',
        login: '',
        password: '',
        endpoint: '',
        shina: '',
        request_type: '',
      },
      formType: {
        name: '',
        desc: '',
      },
      request_types: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'COPY', 'HEAD', 'OPTIONS', 'LINK', 'UNLINK', 'PURGE', 'LOCK', 'UNLOCK', 'PROPFIND', 'VIEW'],
      dialogFormVisible: false,
      typeCreating: false,
      rules: {
        name: [{ required: true, message: 'Name is required', trigger: 'change' }],
        type: [{ required: true, message: 'Type is required', trigger: 'change' }],
        ip: [{ required: true, message: 'IP is required', trigger: 'change' }],
        port: [{ required: true, message: 'Port is required', trigger: 'change' }],
        request_type: [{ required: true, message: 'Request type is required', trigger: 'change' }],
        shina: [{ required: true, message: 'Server type is required', trigger: 'change' }],
      },
    };
  },
  created() {
    this.getTypes();
  },
  methods: {
    resetTemp() {
      this.form = {
        name: '',
        type: '',
        ip: '',
        port: '',
        login: '',
        password: '',
        endpoint: '',
        shina: '',
        request_type: '',
      };
    },
    onSubmit() {
      this.$refs['dataForm'].validate((valid) => {
      if (valid) {
        console.log(valid);
      createServer(this.form).then(response => {
        this.$notify({
          title: 'Success',
          message: 'Created successfully',
          type: 'success',
          duration: 2000,
        });
         this.$router.push({ path: '/table/servers-status'});
      }).catch(error => {
      
        console.log(error);
      })
      // this.resetTemp();
      }
      });
    },
    async onSubmitType() {
      this.typeCreating = true;
      await createServerType(this.formType).then(response => {
        this.$notify({
          title: 'Success',
          message: 'Created successfully',
          type: 'success',
          duration: 2000,
        });
      }).catch(error => {
        console.log(error);
      })
        .finally(() => {
          this.dialogFormVisible = false;
          this.typeCreating = false;
          this.formType.name = '';
          this.formType.desc = '';
          this.getTypes();
        });
    },
    onCancel() {
      this.$message({
        message: 'cancel!',
        type: 'warning',
      });
    },
    handleCreateTypeServer() {
      this.dialogFormVisible = true;
    },
    // handleRefresh()
    // {
    //   this.typeLoading = true;
    //   getServerTypes().then(response => {
    //     response.data.server_types.forEach((eachType, index) => {
    //       this.server_types[index] = eachType['name'];

    //     });
    //     });
    //      this.typeLoading = false;
    // },
    async getTypes() {
      this.typeLoading = true;
      await getServerTypes(this.query).then(response => {
        response.data.server_types.forEach((eachType, index) => {
          this.server_types[index] = eachType['name'];
        });
      });
      this.typeLoading = false;
    },
  },

};
</script>

<style scoped>
.line{
  text-align: center;
}
</style>

