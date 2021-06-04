<template>
  <div class="app-container">
    <el-button class="filter-item" style="margin-right: 60px; margin-bottom: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">
      {{ $t('table.add') }}
    </el-button>
    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>

      <el-table-column min-width="150" align="center" :label="$t('table.name')">
        <template slot-scope="scope">
          <span>{{ scope.row.name | uppercaseFirst }}</span>
        </template>
      </el-table-column>

      <!-- <el-table-column align="left" :label="$t('table.description')">
        <template slot-scope="scope">
          <span>{{ scope.row.description }}</span>
        </template>
      </el-table-column> -->

      <el-table-column v-if="checkPermission(['manage permission'])" align="center" label="Actions" width="200">
        <template slot-scope="scope">
          <el-button v-if="scope.row.name !== 'admin'" v-permission="['manage permission']" type="primary" size="small" icon="el-icon-edit" @click="handleEditPermissions(scope.row.id);">
            {{ $t('permission.editPermission') }}
          </el-button>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Delete" width="200">
        <template slot-scope="scope">
          <el-button v-if="scope.row.name !== 'admin'" v-permission="['manage permission']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id)">
            {{ $t('table.delete') }}
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog :visible.sync="dialogVisible" :title="'Edit Permissions - ' + currentRole.name">
      <div v-loading="dialogLoading" class="form-container">
        <div class="permissions-container">
          <div class="block">
            <el-form :model="currentRole" label-width="80px" label-position="top">
              <el-form-item label="Menus">
                <el-tree ref="menuPermissions" :data="menuPermissions" :default-checked-keys="permissionKeys(roleMenuPermissions)" :props="permissionProps" show-checkbox node-key="id" class="permission-tree" />
              </el-form-item>
            </el-form>
          </div>
          <div class="block">
            <el-form :model="currentRole" label-width="80px" label-position="top">
              <el-form-item label="Permissions">
                <el-tree ref="otherPermissions" :data="otherPermissions" :default-checked-keys="permissionKeys(roleOtherPermissions)" :props="permissionProps" show-checkbox node-key="id" class="permission-tree" />
              </el-form-item>
            </el-form>
          </div>
          <div class="clear-left" />
        </div>
        <div style="text-align:right;">
          <el-button type="danger" @click="dialogVisible=false">
            {{ $t('permission.cancel') }}
          </el-button>
          <el-button type="primary" @click="confirmPermission">
            {{ $t('permission.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
    <el-dialog :title="'Create new role'" :visible.sync="dialogFormVisible">
      <div v-loading="roleCreating" class="form-container">
        <el-form ref="roleForm" :model="newRole" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="$t('user.role')" prop="role">
            <el-input v-model="newRole.name" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="onSubmit">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Resource from '@/api/resource';
import RoleResource from '@/api/role';
import { createRole, deleteRole } from '@/api/role';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive (v-permission)
import checkPermission from '@/utils/permission'; // Permission checking

const roleResource = new RoleResource();
const permissionResource = new Resource('permissions');

export default {
  name: 'RoleList',
  directives: { waves, permission },
  data() {
    return {

      currentRoleId: 1,
      list: [],
      loading: true,
      dialogLoading: false,
      dialogVisible: false,
      dialogFormVisible: false,
      newRole: {
        name: '',
        guard_name: 'api',
      },
      roleCreating: false,
      permissions: [],
      menuPermissions: [],
      otherPermissions: [],
      permissionProps: {
        children: 'children',
        label: 'name',
        disabled: 'disabled',
      },
    };
  },
  computed: {
    currentRole() {
      const found = this.list.find(role => role.id === this.currentRoleId);
      if (found === undefined) {
        return { name: '', permissions: [] };
      }

      return found;
    },
    rolePermissions() {
      return this.classifyPermissions(this.currentRole.permissions).all;
    },
    roleMenuPermissions() {
      return this.classifyPermissions(this.currentRole.permissions).menu;
    },
    roleOtherPermissions() {
      return this.classifyPermissions(this.currentRole.permissions).other;
    },
  },
  created() {
    this.getRoles();
    this.getPermissions();
  },

  methods: {
    checkPermission,
    async getRoles() {
      this.loading = true;
      const { data } = await roleResource.list({});
      this.list = data;
      this.list.forEach((role, index) => {
        role['index'] = index + 1;
        role['description'] = this.$t('roles.description.' + role.name);
      });
      this.loading = false;
    },

    async getPermissions() {
      const { data } = await permissionResource.list({});
      const { all, menu, other } = this.classifyPermissions(data);
      this.permissions = all;
      this.menuPermissions = menu;
      this.otherPermissions = other;
    },
    resetNewRole() {
      this.newRole = {
        name: '',
      };
    },
    handleCreate() {
      this.resetNewRole();
      this.dialogFormVisible = true;
      // this.$nextTick(() => {
      //   this.$refs['userForm'].clearValidate();
      // });
    },
    onSubmit() {
      this.roleCreating = true;
      roleResource
        .store(this.newRole)
        .then(response => {
          console.log(response.error);
          this.$message({
            message: 'New user ' + this.newRole.name + ' has been created successfully.',
            type: 'success',
            duration: 5 * 1000,
          });
          this.resetNewRole();
          this.roleCreating = false;
        })
        .catch(error => {
          console.log(error);
        })
        .finally(() => {
          this.roleCreating = false;
          this.getRoles();
        });

      //     this.roleCreating = true;
      //     await createRole(this.newRole).then(() => {
      //     this.$notify({
      //     title: 'Success',
      //     message: 'Created successfully',
      //     type: 'success',
      //     duration: 2000,
      //   });
      // });
      // this.resetNewRole();
      this.dialogFormVisible = false;

      this.getRoles();
      this.roleCreating = false;
    },
    async handleDelete(id) {
      this.roleCreating = true;
      await deleteRole(id).then(() => {
        this.$notify({
          title: 'Success',
          message: 'Updated successfully',
          type: 'success',
          duration: 2000,
        });
      });

      this.getRoles();
      this.roleCreating = false;
    },
    classifyPermissions(permissions) {
      const all = []; const menu = []; const other = [];
      permissions.forEach(permission => {
        const permissionName = permission.name;
        all.push(permission);
        if (permissionName.startsWith('view menu')) {
          menu.push(this.normalizeMenuPermission(permission));
        } else {
          other.push(this.normalizePermission(permission));
        }
      });
      return { all, menu, other };
    },

    normalizeMenuPermission(permission) {
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name.substring(10)) };
    },

    normalizePermission(permission) {
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name), disabled: permission.name === 'manage permission' };
    },

    permissionKeys(permissions) {
      return permissions.map(permssion => permssion.id);
    },

    handleEditPermissions(id) {
      this.dialogVisible = true;
      this.currentRoleId = id;
      this.$nextTick(() => {
        this.$refs.menuPermissions.setCheckedKeys(this.permissionKeys(this.roleMenuPermissions));
        this.$refs.otherPermissions.setCheckedKeys(this.permissionKeys(this.roleOtherPermissions));
      });
    },

    confirmPermission() {
      const checkedMenu = this.$refs.menuPermissions.getCheckedKeys();
      const checkedOther = this.$refs.otherPermissions.getCheckedKeys();
      const checkedPermissions = checkedMenu.concat(checkedOther);
      this.dialogLoading = true;

      roleResource.update(this.currentRole.id, { permissions: checkedPermissions }).then(response => {
        this.$message({
          message: 'Permissions has been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        });
        this.dialogLoading = false;
        this.dialogVisible = false;
        this.getRoles();
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.permissions-container {
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
