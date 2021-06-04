<template>
  <div class="app-container">
    <el-select v-model="query.type" :placeholder="$t('table.serverType')" clearable class="filter-item" style="margin-left: 4px; margin-bottom: 10px;" @change="handleFilter">
      <el-option v-for="item in server_type" :key="item" :label="item | uppercaseFirst" :value="item" />
    </el-select>
    <!-- Note that row-key is necessary to get a correct row order. -->
    <el-table ref="dragTable" v-loading="listLoading" :data="list" row-key="id" border fit highlight-current-row style="width: 100%" :cell-style="tableRowClassName">
      <el-table-column align="center" label="ID" width="65">
        <template slot-scope="scope" class="working">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>

      <el-table-column width="250px" align="center" label="Host Name">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column width="120px" align="center" label="Type">
        <template slot-scope="scope">
          <span>{{ scope.row.type }}</span>
        </template>
      </el-table-column>

      <el-table-column width="150px" align="center" label="Endpoint">
        <template slot-scope="scope">
          <span>{{ scope.row.endpoint }}</span>
        </template>
      </el-table-column>
      <el-table-column width="150px" align="center" label="Server Type">
        <template slot-scope="scope">
          <span>{{ scope.row.server_type }}</span>
        </template>
      </el-table-column>
      <el-table-column width="110px" align="center" label="Status Code">
        <template slot-scope="scope">
          <span>{{ scope.row.status }}</span>
        </template>
      </el-table-column>

      <el-table-column class-name="status-col" label="Status Text" width="250">
        <template slot-scope="scope">
          <el-tag :type="scope.row.working | statusFilter">
            {{ scope.row.statusText }}
          </el-tag>

        </template>
      </el-table-column>
      <el-table-column min-width="10px" align="center" label="Last Updated">
        <template slot-scope="scope">
          <span>{{ scope.row.updated }}</span>
        </template>
      </el-table-column>
      <el-table-column class-name="status-col" label="Request" width="110">
        <template slot-scope="scope">
          <el-button type="primary" @click="handleRequest(scope.row.id)">
            {{ $t('table.request') }}
          </el-button>
        </template>
      </el-table-column>
      <el-table-column width="110px" align="center" label="Modify">
        <template slot-scope="scope">
          <svg-icon v-role="['admin']" icon-class="edit" @click="handleUpdate(scope.row.id)" />
          / <i v-role="['admin']" class="el-icon-delete" @click="handleDelete(scope.row.id)" />
        </template>
      </el-table-column>
      <el-table-column width="110px" align="center" label="Request Type">
        <template slot-scope="scope">
          <span>{{ scope.row.request_type }}</span>
        </template>
      </el-table-column>
    </el-table>
    <!-- $t is vue-i18n global function to translate lang (lang in @/lang)  -->
    <!-- <div class="show-d">
      {{ $t('table.dragTips1') }} : &nbsp; {{ oldList }}
    </div>
    <div class="show-d">
      {{ $t('table.dragTips2') }} : {{ newList }}
    </div> -->
    <el-dialog :visible.sync="dialogFormVisible">
      <el-form ref="dataForm" label-position="left" label-width="200px" style="width: 600px; margin-left:50px;">
        <el-form-item :label="$t('table.name')" prop="title">
          <el-input v-model="form.editname" value="form.editname" />
        </el-form-item>
        <el-form-item :label="$t('table.type')" prop="title">
          <el-select v-model="form.edittype" placeholder="please select server type">
            <el-option label="Production" value="production" />
            <el-option label="Test" value="test" />
          </el-select>
        </el-form-item>
        <el-form-item :label="$t('table.ip')" prop="title">
          <el-input v-model="form.editip" value="form.editip" />
        </el-form-item>
        <el-form-item :label="$t('table.port')" prop="title">
          <el-input v-model="form.editport" value="form.port" />
        </el-form-item>
        <el-form-item :label="$t('table.requestType')" prop="title">
          <el-select v-model="form.editrequest_type" placeholder="please select server request type" style="margin-right:100px;">
            <el-option v-for="item in request_types" :key="item" :label="item | uppercaseFirst" :value="item" />
          </el-select>
        </el-form-item>
        <el-form-item :label="$t('table.login')" prop="title">
          <el-input v-model="form.editlogin" value="form.login" />
        </el-form-item>
        <el-form-item :label="$t('table.password')" prop="title">
          <el-input v-model="form.editpassword" value="form.password" />
        </el-form-item>
        <el-form-item :label="$t('table.endpoint')" prop="title">
          <el-input v-model="form.editendpoint" value="form.endpoint" />
        </el-form-item>
        <el-form-item :label="$t('table.serverType')" prop="title">
          <el-select v-model="form.editserver_type" placeholder="please select server type">
            <el-option v-for="type in server_types" :key="type" :value="type" :label="type" />
          </el-select>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">
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

import { fetchServers, fetchEditServer, UpdateServer, deleteServer, RequestServer, getServerTypes } from '@/api/servers';
import checkPermission from '@/utils/permission'; // Permission checking
import permission from '@/directive/permission/index.js';
import role from '@/directive/role/index.js';
import Sortable from 'sortablejs';

export default {
  name: 'StatusServers',
  directives: { permission, role },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: 'success',
        0: 'danger',
      };
      return statusMap[status];
    },
  },
  data() {
    return {
      server_types: [],
      form: {
        editid: '',
        editname: '',
        edittype: '',
        editip: '',
        editport: '',
        editlogin: '',
        editpassword: '',
        editendpoint: '',
        editshina: '',
        editserver_type: '',
        text: '',
        editrequest_type: '',
      },
      server_type: [],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        type: '',
        user: '',
      },
      request_types: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'COPY', 'HEAD', 'OPTIONS', 'LINK', 'UNLINK', 'PURGE', 'LOCK', 'UNLOCK', 'PROPFIND', 'VIEW'],
      list: [],
      total: null,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 10,
      },
      sortable: null,
      oldList: [],
      newList: [],
      dialogFormVisible: false,
      id: '',
      rules: {
        type: [{ required: true, message: 'type is required', trigger: 'change' }],
        timestamp: [{ type: 'date', required: true, message: 'timestamp is required', trigger: 'change' }],
        title: [{ required: true, message: 'title is required', trigger: 'blur' }],
      },
    };
  },
  //  mounted() {
  //             this.getList();
  //         },
  created() {
    this.getList();
    setInterval(this.cron, 15000);
  },
  methods: {
    checkPermission,
    async getList() {
      const { limit, page } = this.query;
      this.listLoading = true;
      const { data, meta } = await fetchServers(this.query);
      this.list = data.items;
      this.total = data.total;
      console.log(data.user);
      data.server_types.forEach((eachType, index) => {
        this.server_type[index] = eachType['name'];
      });
      this.listLoading = false;
      this.oldList = this.list.map(v => v.id);
      this.newList = this.oldList.slice();
    //   this.$nextTick(() => {
    //     this.setSort();
    //   });
    },
    // setSort() {
    //   const el = this.$refs.dragTable.$el.querySelectorAll('.el-table__body-wrapper > table > tbody')[0];
    //   this.sortable = Sortable.create(el, {
    //     ghostClass: 'sortable-ghost', // Class name for the drop placeholder,
    //     setData: function(dataTransfer) {
    //       // to avoid Firefox bug
    //       // Detail see : https://github.com/RubaXa/Sortable/issues/1012
    //       dataTransfer.setData('Text', '');
    //     },
    //     onEnd: evt => {
    //       const targetRow = this.list.splice(evt.oldIndex, 1)[0];
    //       this.list.splice(evt.newIndex, 0, targetRow);

    //       // for show the changes, you can delete in you code
    //       const tempIndex = this.newList.splice(evt.oldIndex, 1)[0];
    //       this.newList.splice(evt.newIndex, 0, tempIndex);
    //     },
    //   });
    // },
    tableRowClassName({ row, rowIndex }) {
      if (row.working == 1) {
        return '#E1FFD4';
      } else {
        return '#FFECE5';
      }
    },
    async handleUpdate(id) {
      this.listLoading = true;
      fetchEditServer(id).then(response => {
        this.form.editid = response.data.editItems.id;
        this.form.editname = response.data.editItems.name;
        this.form.edittype = response.data.editItems.type;
        this.form.editip = response.data.editItems.ip;
        this.form.editport = response.data.editItems.port;
        this.form.editlogin = response.data.editItems.login;
        this.form.editpassword = response.data.editItems.password;
        this.form.editendpoint = response.data.editItems.endpoint;
        this.form.editrequest_type = response.data.editItems.request_type;
        this.form.editserver_type = response.data.editItems.server_type;
      });
      await getServerTypes(this.query).then(response => {
        response.data.server_types.forEach((eachType, index) => {
          this.server_types[index] = eachType['name'];
        });
      });
      this.id = id; // copy obj
      this.dialogFormVisible = true;
      this.listLoading = false;
    },
    async handleRequest(id){
      this.listLoading = true;
      await RequestServer(id).then(response => {
        console.log(response);
        this.$notify({
          title: 'Success',
          message: 'Request has been done successfully',
          type: 'success',
          duration: 2000,
        });
      });

      this.getList();
    },
    async update(){
      await UpdateServer(this.form).then(() => {
        this.dialogFormVisible = false;
        this.$notify({
          title: 'Success',
          message: 'Updated successfully',
          type: 'success',
          duration: 2000,
        });
      });

      this.getList();
    },
    async handleDelete(id) {
      await deleteServer(id).then(() => {
        this.$notify({
          title: 'Success',
          message: 'Updated successfully',
          type: 'success',
          duration: 2000,
        });
      });

      this.getList();
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    cron(){
      this.getList();
    },
  },
};
</script>

<style>
.sortable-ghost{
  opacity: .8;
  color: #fff!important;
  background: #42b983!important;
}
.working {
    background: #42b983;
}
</style>

<style scoped>
.icon-star {
  margin-right:2px;
}
.drag-handler {
  width: 20px;
  height: 20px;
  cursor: pointer;
}
.show-d {
  margin-top: 15px;
}
</style>
