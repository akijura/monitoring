import request from '@/utils/request';
import Resource from '@/api/resource';

class UserResource extends Resource {
  constructor() {
    super('users');
  }

  permissions(id) {
    return request({
      url: '/' + this.uri + '/' + id + '/permissions',
      method: 'get',
    });
  }

  updatePermission(id, permissions) {
    return request({
      url: '/' + this.uri + '/' + id + '/permissions',
      method: 'put',
      data: permissions,
    });
  }
}
export function fetchEditUser(id) {
  return request({
    url: '/user/' + id,
    method: 'get',
  });
}
export function UpdateUser(data) {
  return request({
    url: '/user/update',
    method: 'post',
    data,
  });
}
export function getRoles() {
  return request({
    url: '/userroles',
    method: 'get',

  });
}
export { UserResource as default };
