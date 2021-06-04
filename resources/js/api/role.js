import request from '@/utils/request';
import Resource from '@/api/resource';

class RoleResource extends Resource {
  constructor() {
    super('roles');
  }

  permissions(id) {
    return request({
      url: '/' + this.uri + '/' + id + '/permissions',
      method: 'get',
    });
  }
}
export function createRole(data) {
  return request({
    url: '/roles/create',
    method: 'post',
    data,
  });
}
export function deleteRole(id) {
  return request({
    url: '/roles/delete/' + id,
    method: 'delete',
  });
}

export { RoleResource as default };
