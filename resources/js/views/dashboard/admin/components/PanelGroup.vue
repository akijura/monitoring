<template>
  <el-row v-loading="listLoading" :gutter="40" class="panel-group">
    <el-col
      v-for="(item, index) in list"
      :key="index"
      :xs="12"
      :sm="12"
      :lg="6"
      class="card-panel-col"
    >
      <div class="card-panel" @click="handleSetLineChartData(item.name)">
        <div class="card-panel-icon-wrapper icon-people">
          <svg-icon icon-class="international" class-name="card-panel-icon" />
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text" :style="item.name === activeItem ? activePanel : nonActive">
            {{ item.name }}
          </div>

          <count-to :start-val="0" :end-val="item.total" :duration="2600" class="card-panel-num" /><span> Errors</span>
        </div>
      </div>
    </el-col>

  </el-row>
</template>

<script>
import CountTo from 'vue-count-to';
import ServerResource from '@/api/type';
import { fetchErrorsCount } from '@/api/type';
const serverResource = new ServerResource();
export default {
  components: {
    CountTo,
  },

  data() {
    return {
      activePanel: {
        color: 'red',
        fontSize: '30px',
      },
      nonActive: {

      },
      list: [],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        type: '',
        user: '',
      },
      listLoading: true,
      isActive: false,
      activeItem: 'Shina',
      counts: 0,
    };
  },
  created() {
    // this.getList();
    this.countErrors();
  },
  methods: {
    handleSetLineChartData(type) {
      this.activeItem = type;
      this.$emit('handleSetLineChartData', type);
    },
    // async getList() {
    //   const { limit, page } = this.query;
    //   this.listLoading = true;
    //   const { data, meta } = await serverResource.list(this.query);
    //   this.list = data;
    //   this.list.forEach((element, index) => {
    //     console.log(element.name);
    //     element['index'] = (page - 1) * limit + index + 1;
    //   });
    //   this.listLoading = false;
    // },
    async countErrors() {
      this.listLoading = true;
      await fetchErrorsCount().then(response => {
        this.list = response.data.countData;
      });
      this.listLoading = false;
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.panel-group {
  margin-top: 18px;
  .card-panel-col{
    margin-bottom: 32px;
  }
  .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: #666;
    background: #fff;
    box-shadow: 4px 4px 40px rgba(0, 0, 0, .05);
    border-color: rgba(0, 0, 0, .05);
    &:hover {
      .card-panel-icon-wrapper {
        color: #fff;
      }
      .icon-people {
         background: #40c9c6;
      }
      .icon-message {
        background: #36a3f7;
      }
      .icon-money {
        background: #f4516c;
      }
      .icon-shopping {
        background: #34bfa3
      }
    }
    .icon-people {
      color: #40c9c6;
    }
    .icon-message {
      color: #36a3f7;
    }
    .icon-money {
      color: #f4516c;
    }
    .icon-shopping {
      color: #34bfa3
    }
    .card-panel-icon-wrapper {
      float: left;
      margin: 14px 0 0 14px;
      padding: 16px;
      transition: all 0.38s ease-out;
      border-radius: 6px;
    }
    .card-panel-icon {
      float: left;
      font-size: 48px;
    }
    .card-panel-description {
      float: right;
      font-weight: bold;
      margin: 26px;
      margin-left: 0px;
      .card-panel-text {
        line-height: 18px;
        color: rgba(0, 0, 0, 0.45);
        font-size: 23px;
        margin-bottom: 12px;
      }
      .card-panel-num {
        font-size: 20px;
      }
    }
  }
}
</style>
